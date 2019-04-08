<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use PHPShopify;
use App\Shopify\Shopify_POST;

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
        $data = $this->data;

        $config = array(
            'ShopUrl' => 'valedra-test.myshopify.com',
            'ApiKey' => env('SHOPIFY_APIKEY'),
            'Password' => env('SHOPIFY_PASSWORD'));

        PHPShopify\ShopifySDK::config($config);

        $shopify = new PHPShopify\ShopifySDK; # new instance of PHPShopify class

        try
        {
        $customer = Shopify_POST::check_customer_existence($shopify,$data);

        if (empty($customer))
        {
            Shopify_POST::create_customer($shopify,$data);

            if (empty($data["installments"]["installment_1"]))
            {
                Shopify_POST::create_order($shopify,$data);
            }

            elseif (!empty($data["installments"]["installment_1"]))
            {
                Shopify_POST::create_order_with_installment($shopify,$data);
            }
        }

        if (!empty($customer) && empty($data["installments"]["installment_1"]))
        {
            Shopify_POST::create_order($shopify,$data);
        }

        elseif (!empty($data["installments"]["installment_1"]))
        {
            Shopify_POST::create_order_with_installment($shopify, $data);
        }

            $_id = $data["_id"];

        \DB::table('shopify_excel_upload')
                ->where('_id', $_id)
                ->update(['job_status' => 'completed']);

        }
        catch(\Exception $e)
        {
            $_id = $data["_id"];

            \DB::table('shopify_excel_upload')
                ->where('_id', $_id)
                ->update(['job_status' => 'failed']);

            echo "Job Failed";
        }
    }
}
