<?php


namespace App\Models;

class Shopify extends Base
{
    protected $connection = 'mongodb';
    protected $collection = 'shopify_excel_upload';
    protected $guarded = [];

    const STATUS_ONLINE_FAILURE = 100;
    const STATUS_CASH_FAILURE = 101;
    const STATUS_CHEQUE_FAILURE = 102;
    const STATUS_SUCCESS = 200;
}
