<?php
namespace App\Models;

class WebhookNotification extends Base
{

    protected $collection = 'webhook_notifications';

    protected $guarded = [];

    const EVENT = 'event';

    const CHANNEL = 'channel';

    const DATA = 'data';
}
