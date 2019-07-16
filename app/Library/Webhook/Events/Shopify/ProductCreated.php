<?php
namespace App\Library\Webhook\Events\Shopify;

use App\Models\Product;
use App\Models\Webhook;

class ProductCreated
{

    public static function handle(Webhook $Webhook)
    {
        $domain_store = $Webhook->headers('x-shopify-shop-domain', null);
        $product_data = $Webhook->body();
        $product_data['domain_store'] = $domain_store;
        Product::create($product_data);
    }
}