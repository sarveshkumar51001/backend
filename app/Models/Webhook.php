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

    /**
     * Returns headers of webhook request
     *
     * @param string $key
     * @param string $default
     * @return array|mixed
     */
    public function headers($key = null, $default = null)
    {
        $headers = collect($this->{self::DATA}['headers']);

        if (is_null($key)) {
            return $headers->all();
        }

        return head($headers->get($key, $default));
    }

    /**
     * Returns fields of webhook request
     *
     * @return Array
     */
    public function body()
    {
        return $this->{self::DATA}['body'];
    }
}
