<?php
namespace App\Models;

class WebhookNotification extends Base
{

    protected $collection = 'webhook_notifications';

    protected $guarded = [];

    const CHANNEL = 'channel';

    const TO = 'to';

    const WEBHOOK_URL = 'webhook_url';
}
