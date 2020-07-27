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



        // events.valedra.com/online-yoga-at-home
        if ($page_id == 20189025)
        {
//	    	Mail::send('emails.instapage.20189025', $body, function ($message, $body, $email) {
//			    $message->from('support@valedra.com', 'Valedra');
//			    $message->to($email);
//			    $message->attach(storage_path('files/Join Us via Zoom Call.pdf'));
//		    });
        }

        // https://events.valedra.com/online-yoga
        // http://bit.ly/yoga-online-at-home
        elseif ($page_id == 20202660 && time() < 1585708200)
        {
            Mail::send('emails.instapage.20202660', ['body' => $body], function ($message) use($email) {
                $message->from('support@valedra.com', 'Valedra');
                $message->to($email);
                $message->subject('Yoga at Home with Valedra');
                $message->attach(storage_path('files/Join Us via Zoom Call - Yoga.pdf'));
            });
        }

        // https://events.valedra.com/zumba-at-home
        // http://bit.ly/zumba-at-home
        elseif ($page_id == 20221695 && time() < 1585737000)
        {
            self::mail('emails.instapage.20221695', ['body' => $body],
                'Zumba at Home with Valedra', $email,
                storage_path('files/Join Us via Zoom Call - Zumba.pdf'), false);
        }

        // https://events.valedra.com/online-indian-classical-dance?utm_source=sms
        // http://bit.ly/online-indian-classical-dance
        elseif ($page_id == 20233330 && time() < 1585827000) {
            self::mail('emails.instapage.20233330', ['body' => $body],
                'Indian Classical Dance with Valedra', $email,
                storage_path('files/Join Us via Zoom - Indian Classical Dance.pdf'), false);

        }
        // https://events.valedra.com/toppr-access
        elseif ($page_id == 20238395 && time() < 1586975399)
        {
            $codeMapping = [
                "Apeejay School, Pitampura" => 'APJPITAMPURA',
                "Apeejay School, Saket" => 'APEEJAYSAKET',
                "Apeejay School, Sheikh Sarai" => 'APJPPARK',
                "Apeejay School, Noida" => 'APEEJAYNOIDA',
                "Apeejay School, Faridabad" => 'APEEJAYFBD1',
                "Apeejay Svran Global School, Faridabad" => 'APEEJAYFBD21',
                "Apeejay School, Kharghar" => 'APEEJAYKHARGARH',
                "Apeejay School, Nerul" => 'APEEJAYNERUL',
                "Apeejay International School, Greater Noida" => 'APEEJAYGNOIDA',
                "Apeejay School, Mahavir Marg" => 'APJMAHAVIRMARG',
                "Apeejay School, Model Town" => 'APJMODELTOWN',
                "Reynott Academy" => 'REYNOTTACADEMY',
                "Apeejay School, Charkhi Dadri" => 'APJCHARKHIDADRI',
                "Apeejay School, Jalandhar" => 'APEEJAYJUC',
                "Apeejay School, Tanda Road" => 'APJTANDAROAD',
            ];

            $code = $codeMapping[$body["School"]] ?? '';

            self::mail('emails.instapage.20238395', ['body' => $body, 'code' => $code],
                'Access Toppr For Free', $email, storage_path('files/Toppr Brochure.pdf'), false);

        }

        // https://events.valedra.com/byjus-access
        elseif ($page_id == 20261575 && time() < 1586975399) {
            self::mail('emails.instapage.20261575', ['body' => $body],
                'Access BYJU\'s For Free', $email, storage_path('files/BYJUS_Class_1 to 10_Brochure.pdf'), false);
        }

        // https://events.valedra.com/virtual-museum-tours
        elseif ($page_id == 20242715 && time() < 1586255400) {
            self::mail('emails.instapage.20242715', ['body' => $body],
                'Virtual Museum Tour | Valedra', $email,
                storage_path('files/Join Us via Zoom Call _ VIrtual Museum Visits.pdf'), false);
        }

        // https://programs.hayrey.com/webinar-profile-building
        elseif ($page_id == 20250570 && time() < 1585985400) {
            self::mail('emails.instapage.20250570', ['body' => $body],
                'Thank You for Registering | Webinar Log In Credentials', $email,
                storage_path('files/_H&R - Join Us via Zoom GD.pdf'), false);
        }
        else {
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

    private static function mail($view, $view_data, $subject, $email, $attachment, $is_sandbox = false) {
        if($is_sandbox) {
            $email = ['ankur@valedra.com', 'bishwanath@valedra.com'];
        }

        Mail::send($view, $view_data, function ($message) use($email, $subject, $attachment) {
            $message->from('support@valedra.com', 'Valedra');
            $message->subject($subject);
            $message->to($email);
            if(!empty($attachment)) {
                $message->attach($attachment);
            }
        });
    }
}
