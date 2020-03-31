<?php
namespace App\Library\Webhook\Events\Instapage;

use App\Library\Webhook\Channel;
use Illuminate\Support\Facades\Mail;
use App\Library\Instapage\WebhookDataInstapage;
use App\Models\Webhook;
use function slack;

class LeadCreate
{
    public static function handle(Webhook $Webhook)
    {
        self::postToSlack($Webhook);

        self::sendEmail($Webhook, 1);
    }

    private static function postToSlack(Webhook $Webhook)
    {
        return;
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

    private static function sendEmail(Webhook $Webhook, $sandbox = 0) {
	    $body = $Webhook->body();

	    $email = $body['Email'];
	    if($sandbox) {
	        $email = ['ankur@valedra.com', 'bishwanath@valedra.com', 'zuhaib@valedra.com', 'rhea@valedra.com'];
        }
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
        elseif ($page_id == 20202660)
        {
            Mail::send('emails.instapage.20202660', ['body' => $body], function ($message) use($email) {
                $message->from('support@valedra.com', 'Valedra');
                $message->to($email);
                $message->attach(storage_path('files/Join Us via Zoom Call - Yoga.pdf'));
            });
        }

        // https://events.valedra.com/zumba-at-home
        // http://bit.ly/zumba-at-home
        elseif ($page_id == 20221695)
        {
            Mail::send('emails.instapage.20221695', ['body' => $body], function ($message) use($email) {
                $message->from('support@valedra.com', 'Valedra');
                $message->to($email);
                $message->attach(storage_path('files/Join Us via Zoom Call - Zumba.pdf'));
            });
        }
    }
}
