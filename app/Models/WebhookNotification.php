<?php
namespace App\Models;

class WebhookNotification extends Base
{

    protected $collection = 'webhook_notifications';

    protected $guarded = [];

    const CHANNEL = 'channel';

    const TO = 'to';

    const WEBHOOK_URL = 'webhook_url';

    const ADMIN_EMAIL_LIST = [
        'ankur@valedra.com',
        'bishwanath@valedra.com'
    ];

    const CUTOFF_DATE_FORMAT = 'd/m/Y h:i A';
}
