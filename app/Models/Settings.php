<?php
namespace App\Models;

class Settings extends Base
{

    protected $connection = 'mongodb';

    protected $collection = 'settings';

    protected $guarded = [];

}
