<?php

namespace App\Models;

class Webhook extends Base
{
    protected $collection = 'webhooks';

    protected $guarded = [];

    const EVENT = 'event';

    const NAME = 'name';

    const SOURCE = 'source';

    const DATA = 'data';

    const ISAUTHENTICATED = 'is_authenticated';
}
