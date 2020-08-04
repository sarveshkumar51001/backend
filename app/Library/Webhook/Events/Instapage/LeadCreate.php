<?php
namespace App\Library\Webhook\Events\Instapage;

use App\Library\Webhook\Channel;
use App\Models\InstaPage;
use App\Models\WebhookNotification;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Mail;
use App\Library\Instapage\WebhookDataInstapage;
use App\Models\Webhook;
use function slack;

class LeadCreate
{
    public static function handle(Webhook $Webhook)
    {
        self::postToSlack($Webhook);

        self::sendEmail($Webhook);

        self::saveToCollection($Webhook);
    }


    private static function saveToCollection(Webhook $Webhook)
    {
        $page_id = (string) $Webhook->body()['page_id'];

        $doc = [
            "page_id" => $page_id,
            "page_name" => $Webhook->body()['page_name'],
            "page_url" => $Webhook->body()['page_url'],
            "lead_fields" => array_merge(array_keys(WebhookDataInstapage::getFormData($Webhook->body())), ['Captured At'])
        ];

        // Updates already existing Page
        InstaPage::updateOrCreate(
            ['page_id' => $page_id],
            $doc
        );
    }

    private static function postToSlack(Webhook $Webhook)
    {
        $data = WebhookDataInstapage::getFormData($Webhook->body());
        $page_id = $Webhook->body()['page_id'];
        $title = ":tada: New Lead Captured from Page - ".$Webhook->body()['page_name'];

        $channel = Channel::SlackUrl($page_id);

        foreach ($channel as $value) {
            $webhook_url = $value['to']['webhook_url'];
            slack($data, $title)->webhook($webhook_url)
                ->success()
                ->post();
        }
    }


    private static function sendEmail(Webhook $Webhook) {

        $body = $Webhook->body();

        $email = $body['Email'];

        $page_id = $body['page_id'];

        $WebhookNotification = WebhookNotification::where('data.page_id', (string) $page_id)->first();

        if(! $WebhookNotification) {
            return;
        }
        $data = $WebhookNotification->toArray();

        $page_data = $data['data'];
        $email_field = $page_data['to_email'];
        $sending_from = $page_data['sending_from'] ?? 'support@valedra.com';
        $sending_name = WebhookNotification::$sending_data[$sending_from] ?? 'Valedra';

        if(!isset($body[$email_field])) {
            throw new \Exception("Email field not found");
        }
        $email = $body[$email_field];

        $blade = Blade::compileString(html_entity_decode($page_data['template'], ENT_QUOTES, 'UTF-8'));
        $view = string_view_renderer($blade, [
            'body' => $body
        ]);

        if($data && $page_data['cutoff_datetime'] > time() && $page_data['active']){

            if($page_data['test_mode']) {
                $email = WebhookNotification::ADMIN_EMAIL_LIST;
            }
            // $page_id = 20633953 https://school.apeejay.edu/session-registration
            // $page_id = 20649291 https://school.apeejay.edu/parent-registration
            $from = [
                "email" => in_array($page_id, [20633953, 20649291]) ? 'admissions@academy.apeejay.edu' : $sending_from,
                "name" => in_array($page_id, [20633953, 20649291]) ? 'Apeejay Academy' : $sending_name
            ];

            Mail::send( [], [], function ($message) use($email,$page_data,$view,$from) {
                $message->from($from['email'], $from['name']);
                $message->subject($page_data['subject']);
                $message->to($email);
                $message->setBody($view,'text/html');
                if(!empty($page_data['attachments'])) {
                    foreach ($page_data['attachments'] as $attachment) {
                        $message->attach($attachment);
                    }
                }
            });
        }
    }
}
