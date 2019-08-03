<?php
namespace App\Library\Webhook\Events\Shopify;

use App\Library\Shopify\WebhookDataShopify;
use App\Library\Webhook\Channel;
use App\Models\Webhook;
use App\Models\Order;

class OrderCreate
{

    public static function handle(Webhook $Webhook)
    {
        $domain_store = $Webhook->headers('x-shopify-shop-domain', null);
        $order_data = $Webhook->body();
        $order_data['domain_store'] = $domain_store;
        Order::create($order_data);

        self::postToSlack($Webhook);
    }

    private static function postToSlack(Webhook $Webhook)
    {
        $data = WebhookDataShopify::getFormData($Webhook->body());
        $store_name = $Webhook->body()['vendor'];
        $title = ":tada: You have a New Order - ".$Webhook->body()['name'];

        $channel_url = Channel::SlackUrl($store_name);

        slack($data, $title)->webhook($channel_url)
            ->success()
            ->post();
    }
}