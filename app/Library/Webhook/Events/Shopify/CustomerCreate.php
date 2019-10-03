<?php
namespace App\Library\Webhook\Events\Shopify;

use App\Library\Shopify\WebhookDataShopify;
use App\Library\Webhook\Channel;
use App\Models\Webhook;
use App\Models\Customer;

class CustomerCreate {

    public static function handle(Webhook $Webhook)
    {
        $domain_store = $Webhook->headers('x-shopify-shop-domain', null);
        $customer_data = $Webhook->body();
        Customer::create($customer_data);
    }
}
