<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use PHPShopify;
use App\Shopify\shopify_post;

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
        logger($this->data);
        $data = $this->data;

        $config = array(
            'ShopUrl' => 'valedra-test.myshopify.com',
            'ApiKey' => env('SHOPIFY_APIKEY'),
            'Password' => env('SHOPIFY_PASSWORD'));

        PHPShopify\ShopifySDK::config($config);
        $shopify = new PHPShopify\ShopifySDK; # new instance of PHPShopify class

        try {
            $_id = $data["_id"];
            $customer = Shopify_POST::check_customer_existence($shopify, $data);
            $details = Shopify_POST::get_variant_id($data);

            if(empty($data["order_id"]) && $data["job_status"] == 'pending')
            {
                if (empty($customer)) {
                    Shopify_POST::create_customer($shopify, $data);
                    if (empty(array_filter($data["installments"][1]))) {
                        Shopify_POST::create_order($shopify, $data, $details);
                        \DB::table('shopify_excel_upload')->where('_id', $_id)->update(['job_status' => 'completed']);
                    } else {
                        Shopify_POST::create_order_with_installment($shopify, $data, $details);
                        Shopify_POST::post_transaction_for_installment($shopify, $data);
                    }
                }
                if (!empty($customer) && empty(array_filter($data["installments"][1]))) {
                    Shopify_POST::create_order($shopify, $data, $details);
                    \DB::table('shopify_excel_upload')->where('_id', $_id)->update(['job_status' => 'completed']);
                } else {
                    Shopify_POST::create_order_with_installment($shopify, $data, $details);
                    Shopify_POST::post_transaction_for_installment($shopify, $data);
                }
            }
            elseif (!empty($data["order_id"]) && !empty(array_filter($data["installments"][1])) && ($data["job_status"] == 'pending')){
                Shopify_POST::post_transaction_for_installment($shopify,$data);
            }
            else{
                echo "Order created";
            }
        } catch(\Exception $e) {
            $_id = $data["_id"];
            \DB::table('shopify_excel_upload')
                ->where('_id', $_id)
                ->update(['job_status' => 'failed']);

            $this->fail($e);
        }
    }
}