<?php
namespace App\Library\Webhook\Events\Shopify;

use App\Library\Shopify\WebhookDataShopify;
use App\Library\Webhook\Channel;
use App\Models\Webhook;
use App\Models\Order;

class OrderUpdate
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
        $items = '';
        $data = WebhookDataShopify::getFormData($Webhook->body());
        $base_url = "<https://".$Webhook->headers()['x-shopify-shop-domain'][0]."/admin/";

        # Custom data entries and hyperlinks
        $title = $base_url."orders/".$Webhook->body()['id']."|:tada: Order Updated - ".$Webhook->body()['name'].">";
        $data['Customer Name & Email'] = $base_url."customers/".$data['customer']['id']."|".$data['customer']['first_name'].' '.$data['customer']['last_name'].', '.$data['customer']['email'].">";

        foreach ($data['line_items'] as $key => $value) {
            $items .= $base_url."products/".$value['product_id']."|".$value['title'].' X '.$value['quantity'].">\n";
        }
        $data['Item X Quantity'] = $items;
        $data['Total Price'] = $data['total_price']. " ".$data['currency'];
        $data['Total Discount'] = $data['total_discounts']. " ".$data['currency'];

        $data = array_except($data, ['line_items', 'customer', 'total_price', 'total_discounts', 'currency']);

        # Slack channel notifications
        $channel = Channel::SlackUrl("");
        
        foreach ($channel as $value) {
            $webhook_url = $value['to']['webhook_url'];
            slack($data, $title)->webhook($webhook_url)
                ->success()
                ->post();
        }
    }
}