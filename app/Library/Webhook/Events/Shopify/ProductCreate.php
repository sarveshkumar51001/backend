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
        $data = WebhookDataShopify::getFormData($Webhook->body());
        $store_name = $Webhook->body()['vendor'];
        $base_url = "<https://".$Webhook->headers()['x-shopify-shop-domain'][0]."/admin/";
        
        $title = $base_url."products/".$Webhook->body()['id']."|:tada: New Product Created - ".$Webhook->body()['title'].">";

        $channel = Channel::SlackUrl($store_name);
        
        foreach ($channel as $value) {
            $webhook_url = $value['to']['webhook_url'];
            slack($data, $title)->webhook($webhook_url)
                ->success()
                ->post();
        }
    }
}
