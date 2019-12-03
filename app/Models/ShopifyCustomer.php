<?php

namespace App\Models;

class ShopifyCustomer extends Base
{
    protected $connection = 'mongodb';
    protected $collection = 'shopify_customers';
    protected $guarded = [];
}
