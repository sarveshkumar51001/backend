<?php

namespace App\Models;

class Product extends Base
{
	protected $connection = 'mongodb';
	protected $collection = 'shopify_products';
	protected $guarded = [];

}