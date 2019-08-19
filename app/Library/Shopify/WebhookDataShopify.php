<?php
namespace App\Library\Shopify;

use App\Models\Webhook;

class WebhookDataShopify
{
    public static function get_baseUrl(Webhook $Webhook) {

        $url = sprintf("https://%s/admin/", $Webhook->headers('x-shopify-shop-domain', null));
        return $url;
    }

    public static function product_data(Webhook $Webhook) {

        $product_data = array();
        $data = $Webhook->body();

        $product_data['Vendor'] = $data['vendor'];
        $product_data['Product Type'] = $data['product_type'];
        $product_data['Tags'] = $data['tags'];

        return $product_data;
    }
    
    public static function order_data(Webhook $Webhook) {

        $items = "";
        $order_data = array();
        $data = $Webhook->body();
        $base_url = self::get_baseUrl($Webhook);

        $order_data['Customer Name & Email'] = "";
        $order_data['Item X Quantity'] = "";
        $order_data['Total Price'] = "";
        $order_data['Total Discount'] = "";

        # Custom order data entries and hyperlinks
        if (array_key_exists('customer', $data)) {
            $order_data['Customer Name & Email'] = sprintf("<%scustomers/%s|%s %s, %s>", $base_url, $data['customer']['id'], $data['customer']['first_name'], $data['customer']['last_name'], $data['customer']['email']);
        }

        if (array_key_exists('line_items', $data)) {

            foreach ($data['line_items'] as $key => $value) {

                if (!is_null($value['variant_id'])) {

                    $temp_url = sprintf("<%sproducts/%s|%sX%s>\n", $base_url, $value['product_id'], $value['title'], $value['quantity']);
                    $items .= $temp_url;

                } else { $items .= $value['title'].' X '.$value['quantity']."\n"; }
            }
            $order_data['Item X Quantity'] = $items;
        }

        if (array_key_exists('total_price', $data)) {
            $order_data['Total Price'] = sprintf("%s %s", $data['total_price'], $data['currency']);
        }

        if (array_key_exists('total_discounts', $data)) {
            $order_data['Total Discount'] = sprintf("%s %s", $data['total_discounts'], $data['currency']);
        }

        return $order_data;
    }
}