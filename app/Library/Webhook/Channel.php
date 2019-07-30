<?php
namespace App\Library\Webhook;

use App\Models\WebhookNotification;

class Channel
{
    public static function SlackUrl($identifier)
    {
        $response = WebhookNotification::whereIn('identifier', [$identifier, 'all'])->get();
        return $response;
    }
}