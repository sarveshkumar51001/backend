<?php
namespace App\Library\Webhook\Events\Shopify;

use App\Models\Product;
use App\Models\Webhook;
use App\Library\Shopify\WebhookDataShopify;
use App\Library\Webhook\Channel;

class ProductUpdate
{

    public static function handle(Webhook $Webhook)
    {
        $new_product_data = $Webhook->body();
        $product_id = $new_product_data['id'];
        Product::where('id', $product_id)->update($new_product_data);

        self::postToSlack($Webhook);
    }

    private static function postToSlack(Webhook $Webhook)
    {
        $data = WebhookDataShopify::getFormData($Webhook->body());
        $store_name = $Webhook->body()['vendor'];
        $title = ":tada: Product Updated - " . $Webhook->body()['title'];

        $channel = Channel::SlackUrl($store_name);

        foreach ($channel as $value) {
            $webhook_url = $value['to']['webhook_url'];
            slack($data, $title)->webhook($webhook_url)
                ->success()
                ->post();
        }
    }
}