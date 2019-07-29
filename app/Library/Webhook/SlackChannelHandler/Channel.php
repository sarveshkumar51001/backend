<?php
namespace App\Library\Webhook\SlackChannelHandler;

use App\Models\WebhookNotification;

class Channel
{
    public static function SlackUrl($channel)
    {
        $response = WebhookNotification::whereIn('identifier', [$channel, 'all'])->get();
        return $response;
    }
}