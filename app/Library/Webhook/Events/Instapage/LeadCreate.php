<?php
namespace App\Library\Webhook\Events\Instapage;

use App\Library\Webhook\SlackChannelHandler\Channel;
use App\Library\Instapage\WebhookDataInstapage;
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
        $data = WebhookDataInstapage::getFormData($Webhook->body());
        $page_id = $Webhook->body()['page_id'];
        $title = ":tada: New Lead Captured from Page - ".$Webhook->body()['page_name'];

        unset($data['page_id']);
        unset($data['page_name']);

        $channel_url = Channel::SlackUrl($page_id);

        slack($data, $title)->webhook($channel_url)
            ->success()
            ->post();
    }
}