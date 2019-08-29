<?php
namespace App\Library\Webhook\Events\Shopify;

use App\Library\Shopify\WebhookDataShopify;
use App\Library\Webhook\Channel;
use App\Models\Webhook;
use App\Models\Order;

class OrderCreate
{
    public static function handle(Webhook $Webhook) {
        $domain_store = $Webhook->headers('x-shopify-shop-domain', null);
        $order_data = $Webhook->body();
        $order_data['domain_store'] = $domain_store;
        Order::create($order_data);

        self::postToSlack($Webhook);
    }
    
    private static function postToSlack(Webhook $Webhook) {
        $base_url = WebhookDataShopify::get_baseUrl($Webhook);
        $data = WebhookDataShopify::order_data($Webhook);
        
        $title = sprintf("<%sorders/%s|:tada: You have a New Order - %s>", $base_url, $Webhook->body()['id'], $Webhook->body()['name']);

        $channel = Channel::SlackUrl("");
        
        foreach ($channel as $value) {
            $webhook_url = $value['to']['webhook_url'];
            slack($data, $title)->webhook($webhook_url)
                ->success()
                ->post();
        }
    }
}