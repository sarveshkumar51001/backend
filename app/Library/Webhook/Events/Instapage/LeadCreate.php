<?php
namespace App\Library\Webhook\Events\Instapage;

use App\Library\Instapage\Handler;
use App\Models\Webhook;
use function slack;

class LeadCreate
{

    public static function handle(Webhook $Webhook)
    {
        self::postToSlack($Webhook);
    }
    private static function postToSlack(Webhook $Webhook)
    {
        $data = Handler::getFormData($Webhook->body());
        $title = ":tada: New Lead Captured from Page - ".$Webhook->body()['page_name'];

        slack($data, $title)->webhook(env('SLACK_INSTAPAGE_WEBHOOK'), null)
            ->success()
            ->post();
    }
}