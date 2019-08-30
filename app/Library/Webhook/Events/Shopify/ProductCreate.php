<?php
namespace App\Library\Webhook\Events\Shopify;

use App\Library\Shopify\WebhookDataShopify;
use App\Library\Webhook\Channel;
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
        $domain_store = $Webhook->headers('x-shopify-shop-domain', null);
        $store_name = explode('.', $domain_store)[0];
        $channel_identifier = sprintf("%s-product-created", $store_name);

        $base_url = WebhookDataShopify::get_baseUrl($Webhook);
        $data = WebhookDataShopify::product_data($Webhook);

        $title = sprintf("<%sorders/%s | :tada: New Product Created -  %s>", $base_url, $Webhook->body()['id'], $Webhook->body()['title']);

        $channel = Channel::SlackUrl($channel_identifier);
        foreach ($channel as $value) {
            $webhook_url = $value['to']['webhook_url'];
            slack($data, $title)->webhook($webhook_url)
                ->success()
                ->notShort()
                ->post();
        }
    }
}
