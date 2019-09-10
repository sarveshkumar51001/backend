<?php
namespace App\Library\Webhook\Events\Shopify;

use App\Models\Webhook;

class OrderCancel
{
    public static function handle(Webhook $Webhook)
    {
        $domain_store = $Webhook->headers('x-shopify-shop-domain', null);
        $order_data = $Webhook->body();
        logger($order_data);
    }
}
