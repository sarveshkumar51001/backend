<?php
namespace App\Library\Webhook\Events\Shopify;

use App\Library\Webhook\SlackChannelHandler\Channel;
use App\Library\Shopify\WebhookDataShopify;
use App\Models\Product;
use App\Models\Webhook;

class ProductCreate
{

    public static function handle(Webhook $Webhook)
    {
        $domain_store = $Webhook->headers('x-shopify-shop-domain', null);
        $product_data = $Webhook->body();
        $product_data['domain_store'] = $domain_store;
        Product::create($product_data);

        self::postToSlack($Webhook);
    }

    private static function postToSlack(Webhook $Webhook)
    {
        $data = WebhookDataShopify::getFormData($Webhook->body());
        $store_name = $Webhook->body()['vendor'];
        $title = ":tada: New Product Created - ".$Webhook->body()['title'];

        $channel_url = Channel::SlackUrl($store_name);

        slack($data, $title)->webhook($channel_url)
            ->success()
            ->post();
    }
}