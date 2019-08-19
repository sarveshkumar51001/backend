<?php
namespace App\Library\Webhook\Events\Shopify;

use App\Library\Shopify\WebhookDataShopify;
use App\Library\Webhook\Channel;
use App\Models\Product;
use App\Models\Webhook;

class ProductUpdate
{

    public static function handle(Webhook $Webhook) {
        $new_product_data = $Webhook->body();
        $product_id = $new_product_data['id'];
        
        if($Product = Product::where('id', $product_id)->first()) {
            $Product->update($new_product_data);
        } else {
            $domain_store = $Webhook->headers('x-shopify-shop-domain', null);
            $new_product_data['domain_store'] = $domain_store;
            Product::create($new_product_data);
        }

        self::postToSlack($Webhook);
    }

    private static function postToSlack(Webhook $Webhook) {
        $store_name = explode('.', $Webhook->headers('x-shopify-shop-domain', null))[0];

        $base_url = WebhookDataShopify::get_baseUrl($Webhook);
        $data = WebhookDataShopify::product_data($Webhook);
        
        $title = sprintf("<%sorders/%s | :tada: Product Updated - %s>", $base_url, $Webhook->body()['id'], $Webhook->body()['title']);

        $channel = Channel::SlackUrl($store_name);
        
        foreach ($channel as $value) {
            $webhook_url = $value['to']['webhook_url'];
            slack($data, $title)->webhook($webhook_url)
                ->success()
                ->post();
        }
    }
}