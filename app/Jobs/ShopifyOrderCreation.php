<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use PHPShopify;

class ShopifyOrderCreation implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function handle()
    {
        $config = array(
            'ShopUrl' => 'valedra-test.myshopify.com',
            'ApiKey' => env('SHOPIFY_APIKEY'),
            'Password' => env('SHOPIFY_PASSWORD'));

        PHPShopify\ShopifySDK::config($config);

        $shopify = new PHPShopify\ShopifySDK; # new instance of PHPShopify class

        $customers = $shopify->Customer->search("phone:9514254601");
        if (empty($customers)) {
            $customer_data = array(
                "customer" => array(
                    "first_name" => $this->data["student_first_name"],
                    "last_name" => $this->data["student_last_name"],
                    "email" => $this->data["email_id"],
                    "phone" => $this->data["mobile_number"],
                    "verified_email" => true,

                    ));
            $shopify->Customer->post($customer_data);

        }
        else
            $order_data = array (
                "email" => $this->data["email_id"],
                "line_items" => [
                    [
                        "sku" => $this->data["shopify_activity_id"]
                    ]
                ]
            );
        $shopify->Order->post($order_data);



    }
}
