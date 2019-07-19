<?php
namespace App\Library\Webhook\Events\Shopify;

use App\Models\Product;
use App\Models\Webhook;

class ProductUpdate
{

    public static function handle(Webhook $Webhook)
    {
        $new_product_data = $Webhook->body();
        $product_id = $new_product_data['id'];
        Product::where('id', $product_id)->update($new_product_data);
    }
}