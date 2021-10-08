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
    public static $sending_data = [
        'support@valedra.com' => "Valedra",
        'admissions@hayrey.com' => "H&R Admissions",
        'contact@reynott.com' => 'Reynott Contact',
        'admissions@academy.apeejay.edu' => "Appejay",
    ];

    const CUTOFF_DATE_FORMAT = 'd/m/Y h:i A';
}
