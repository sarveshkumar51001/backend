<?php

namespace App\Models;



class InstaPage extends Base
{
    protected $connection = 'mongodb';
    protected $collection = 'webhook_instapage_pages';
    protected $guarded = [];

    const PageId = 'page_id';
    const PageName = 'page_name';
    const PageUrl = 'page_url';

}
