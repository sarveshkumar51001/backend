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

	    $page_id = $body['page_id'];
	    if ($page_id == 20189025)
	    {
	    	Mail::send('emails.20189025', $body, function ($message, $body) {
			    $message->from('support@valedra.com', 'Valedra');
			    $message->to($body['Email']);
			    $message->attach(storage_path('files/Join Us via Zoom Call.pdf'));
		    });
	    }
    }
}