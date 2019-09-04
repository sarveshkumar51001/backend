<?php
namespace App\Models;

class Settings extends Base
{

    protected $connection = 'mongodb';

    protected $collection = 'settings';

    protected $guarded = [];

    public static function get_session_values()
    {
        $session_list = Settings::where('type','bulk_upload')->where('name','session')->first(['value'])->toArray()['value'];

        return $session_list;

    }
}
