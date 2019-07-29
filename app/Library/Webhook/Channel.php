<?php
namespace App\Library\Webhook;

use App\Models\WebhookNotification;

class Channel
{
    public static function SlackUrl($channel)
    {
        $response = WebhookNotification::whereIn('identifier', [$channel, 'all'])->get();
        return $response;
    }
}