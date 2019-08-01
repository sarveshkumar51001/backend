<?php

namespace App\Models;

class Product extends Base
{
	protected $connection = 'mongodb';
	protected $collection = 'shopify_products';
	protected $guarded = [];


	public function scopeActiveProduct($query)
    {
        return $query->where('domain_store',env('SHOPIFY_STORE'))->where('published_at','!=',null)->where('variants.inventory_quantity','>',0);
    }  
}