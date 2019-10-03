<?php
namespace App\Library\Webhook\Events\Shopify;

use App\Library\Shopify\WebhookDataShopify;
use App\Library\Webhook\Channel;
use App\Models\Webhook;
use App\Models\Customer;

class CustomerUpdate{

    public static function handle(Webhook $Webhook)
    {
        $new_customer_data = $Webhook->body();
        $product_id = $new_customer_data['id'];

        if ($Customer = Customer::where('id', $product_id)->first()) {
            $Customer->update($new_customer_data);
        } else {
            $domain_store = $Webhook->headers('x-shopify-shop-domain', null);
            $new_customer_data['domain_store'] = $domain_store;
            Customer::create($new_customer_data);
        }
    }
}
