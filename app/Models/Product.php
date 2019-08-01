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

    public function scopeSearchProduct($query)
    {
        return $query->orwhere('id', 'like', "%$this->query%")
	                   	->orWhere('title', 'like', "%$this->query%")
	                   	->orWhere('product_type', 'like', "%$this->query%")
	                   	->orWhere('tags', 'like', "%$this->query%")
	                   	->orWhere('variants.sku', 'like', "%$this->query%");
    }

}