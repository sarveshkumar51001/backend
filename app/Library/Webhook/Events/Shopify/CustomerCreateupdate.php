<?php
namespace App\Library\Webhook\Events\Shopify;

use App\Library\Shopify\WebhookDataShopify;
use App\Library\Webhook\Channel;
use App\Models\Webhook;
use App\Models\Customer;

class CustomerCreateupdate{

    public static function handle(Webhook $Webhook)
    {
        $new_customer_data = $Webhook->body();
        $customer_id = $new_customer_data['id'];

        if ($Customer = Customer::where('id', $customer_id)->first()) {
            $slack_title = 'update';
            $Customer->update($new_customer_data);
        } else {
            $slack_title = 'create';
            $domain_store = $Webhook->headers('x-shopify-shop-domain', null);
            $new_customer_data['domain_store'] = $domain_store;
            Customer::create($new_customer_data);
        }
        self::postToSlack($Webhook,$slack_title);
    }

    private static function postToSlack(Webhook $Webhook, $slack_title)
    {
        $domain_store = $Webhook->headers('x-shopify-shop-domain', null);
        $store_name = explode('.', $domain_store)[0];
        $base_url = WebhookDataShopify::get_baseUrl($Webhook);
        $data = WebhookDataShopify::customer_data($Webhook,$base_url);

        if($slack_title == 'create') {
            $title = ":tada: New Customer Created";
            $channel_identifier = sprintf("%s-customer-created", $store_name);
        }else {
            $title = "An Existing Customer Has Been Updated";
            $channel_identifier = sprintf("%s-customer-updated", $store_name);
        }

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
