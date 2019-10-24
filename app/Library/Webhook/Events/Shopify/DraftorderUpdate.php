<?php
namespace App\Library\Webhook\Events\Shopify;

use App\Library\Webhook\Channel;
use App\Models\ShopifyExcelUpload;
use App\Models\Webhook;

class DraftorderUpdate
{

    public static function handle(Webhook $Webhook)
    {
        $draft_order_data = $Webhook->body();

        $draft_order_id = $draft_order_data['id'];
        $order_id = $draft_order_data['order_id'];

        if(!empty($order_id)){
            ShopifyExcelUpload::where('order_id',$draft_order_id)->unset('checkout_url');
            ShopifyExcelUpload::where('order_id',$draft_order_id)->update(['order_id'=> $order_id,'job_status' => 'completed']);
        }
    }
}
