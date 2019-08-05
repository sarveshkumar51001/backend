<?php
namespace App\Library\Shopify;

use App\Models\Webhook;

class WebhookDataShopify
{
    public static function product_data(Webhook $Webhook) {
        
        $product_data = array();
        $data = $Webhook->body();
        $base_url = "<https://".$Webhook->headers()['x-shopify-shop-domain'][0]."/admin/";

        $product_data['Vendor'] = $data['vendor'];
        $product_data['Product Type'] = $data['product_type'];
        $product_data['Tags'] = $data['tags'];

        return $product_data;
    }
    
    public static function order_data(Webhook $Webhook) {

        $items = "";
        $order_data = array();
        $data = $Webhook->body();
        $base_url = "<https://".$Webhook->headers()['x-shopify-shop-domain'][0]."/admin/";

        # Custom order data entries and hyperlinks
        if (array_key_exists('customer', $data)) {
            $order_data['Customer Name & Email'] = $base_url."customers/".$data['customer']['id']."|".$data['customer']['first_name'].' '.$data['customer']['last_name'].', '.$data['customer']['email'].">";
        } else { $order_data['Customer Name & Email'] = ""; }

        if (array_key_exists('line_items', $data)) {
            foreach ($data['line_items'] as $key => $value) {
                $items .= $base_url."products/".$value['product_id']."|".$value['title'].' X '.$value['quantity'].">\n";
            }
            $order_data['Item X Quantity'] = $items;
        } else { $order_data['Item X Quantity'] = ""; }

        if (array_key_exists('total_discounts', $data)) {
            $order_data['Total Price'] = $data['total_price']. " ".$data['currency'];
        } else { $order_data['Total Price'] = ""; }

        if (array_key_exists('total_discounts', $data)) {
            $order_data['Total Discount'] = $data['total_discounts']. " ".$data['currency'];
        } else { $order_data['Total Discount'] = ""; }

        return $order_data;
    }
}