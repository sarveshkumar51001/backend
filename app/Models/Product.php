<?php
namespace App\Models;

class Product extends Base
{

    protected $connection = 'mongodb';

    protected $collection = 'shopify_products';

    protected $guarded = [];

    // Active Product (Def.) - Product associated with the current store and available on online store.
    public function scopeActiveProduct($query)
    {
        return $query->where('domain_store', env('SHOPIFY_STORE'))
            ->where('published_at', '!=', null);
    }
}