<?php
namespace App\Library\Webhook\SlackChannelHandler;

class Channel
{
    public static function SlackUrl($channel)
    {
        $response = \DB::collection('webhook_notifications')->where('identifier', $channel)->first();
        return $response['to']['webhook_url'];
    }
}