<?php
namespace App\Library\Webhook\Events\Instapage;

use App\Library\Webhook\Channel;
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

	    // https://events.valedra.com/virtual-museum-tours
        elseif ($page_id == 20242715 && time() < 1586169000) {
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
            $data = WebhookNotification::where('data.page_id',$page_id)->first()->toArray();
            $name_field = $data['data']['to_name'];
            $email_field = $data['data']['to_email'];

            $name = isset($body[$name_field]) ? $body[$name_field] : '';
            $email = isset($body[$email_field]) ? $body[$email_field] : "test@valedra.com";

            $blade = Blade::compileString($data['data']['template']);
            $view = string_view_renderer($blade, [
                'first_name' => $name]);

            if($data && strtotime($data['data']['cutoff_datetime']) > time() && !$data['data']['test_mode'] && $data['data']['active']){

                Mail::send( [], [], function ($message) use($email,$data,$view) {
                    $message->from('support@valedra.com', 'Valedra');
                    $message->subject($data['data']['subject']);
                    $message->to($email);
                    $message->setBody($view,'text/html');
                    foreach($data['data']['attachments'] as $attachment){
                        $message->attach($attachment);
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
            $message->attach($attachment);
        });
    }
}
