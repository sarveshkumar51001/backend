<?php

namespace App\Shopify;
use Carbon\Carbon;
use PHPShopify;

Class Shopify_POST
{
    public static function check_customer_existence($shopify, $customer_info)
    {
        $email = $customer_info["email_id"];
        $phone = $customer_info["mobile_number"];
        $query = sprintf("email:%s OR phone:%s", $email, $phone);
        $customers = $shopify->Customer->search($query);
        return $customers;
    }

    public static function get_variant_id($product_info)
    {
        $variant_id = \DB::table('valedra_products')->where('product_sku', $product_info['shopify_activity_id'])->get()->first();
        return $variant_id;
    }

    public static function create_customer($shopify, $customer_info)
    {
        $customer_data = [
            "first_name" => $customer_info["student_first_name"],
            "last_name" => $customer_info["student_last_name"],
            "email" => $customer_info["email_id"],
            "phone" => (string)$customer_info["mobile_number"],
            "verified_email" => true,
            "metafields" => [[
                "key" => "School Name",
                "value" => $customer_info["school_name"],
                "value_type" => "string",
                "namespace" => "global"
            ], [
                "key" => "Class",
                "value" => $customer_info["class"],
                "value_type" => "integer",
                "namespace" => "global"
            ], [
                "key" => "Section",
                "value" => $customer_info["section"],
                "value_type" => "string",
                "namespace" => "global"
            ], [
                "key" => "School Enrollment No.",
                "value" => $customer_info["school_enrollment_no"],
                "value_type" => "string",
                "namespace" => "global"
            ], [
                "key" => "Parent First Name",
                "value" => $customer_info["parent_first_name"],
                "value_type" => "string",
                "namespace" => "global"
            ], [
                "key" => "Parent Last Name",
                "value" => $customer_info["parent_last_name"],
                "value_type" => "string",
                "namespace" => "global"]]
        ];
        $shopify->Customer->post($customer_data);
    }

    public static function create_order($shopify, $order_info, $details)
    {
        $_id = $order_info['_id'];
        $order_data = [
            "email" => $order_info["email_id"],
            "line_items" => [[
                "variant_id" => $details['product_id']
            ]],
            "transaction" => [[
                "kind" => "capture"
            ]],
            "note_attributes" => [[
                "name" => "Payment Mode",
                "value" => $order_info["mode_of_payment"]
            ], [
                "name" => "Cheque/DD No.",
                "value" => $order_info["chequedd_no"]
            ], [
                "name" => "Cheque/DD Date",
                "value" => $order_info["chequedd_date"]
            ], [
                "name" => "Online Transaction Reference Number",
                "value" => $order_info["txn_reference_number_only_in_case_of_paytm_or_online"]
            ], [
                "name" => "Drawee Name",
                "value" => $order_info["drawee_name"]
            ], [
                "name" => "Drawee Account Number",
                "value" => $order_info["drawee_account_number"]
            ], [
                "name" => "MICR Code",
                "value" => $order_info["micr_code"]
            ], [
                "name" => "Bank Name",
                "value" => $order_info["bank_name"]
            ], [
                "name" => "Branch Name",
                "value" => $order_info["bank_branch"]
            ]]
        ];

        $order_response = $shopify->Order->post($order_data);
        \DB::table('shopify_excel_upload')->where('_id', $_id)->update(['order_id'=> $order_response["id"]]);
    }

    public static function create_order_with_installment($shopify, $order_info, $details)
    {
        $_id = $order_info['_id'];
        $order_data = [
            "email" => $order_info["email_id"],
            "line_items" => [[
                "variant_id" => $details['product_id'],
                "quantity" => 1
            ]],
            "transactions" => [[
                "amount" => $order_info['final_fee_incl_gst'],
                "kind" => "authorization"
            ]],
            "financial_status" => "pending"
        ];
        $order_object = $shopify->Order->post($order_data);
        \DB::table('shopify_excel_upload')->where('_id', $_id)->update(['order_id'=> $order_object["id"]]);

    }

    public static function post_transaction_for_installment(PHPShopify\ShopifySDK $shopify, $order_details)
    {
        $_id = $order_details['_id'];
        $order_id = $order_details['order_id'];

        for ($i = 1; $i <= 5; $i++) {

            $installment_index = sprintf("installments.%s.processed", $i);


            $input = $order_details['installments'][$i];

            $output = implode(', ', array_map(function ($v, $k) {
                return sprintf("%s - %s\n", $k, $v);
            }, $input, array_keys($input)));

            if ($order_details['installments'][$i]['processed'] == 'No') {

                $transaction_data = [
                        "kind" => "capture",
                        "amount" => $order_details['installments'][$i]['installment_amount']
                    ];
                $installment_details = [
                    "note_attributes" => [[
                        "name" => sprintf("Installment-%s", $i),
                        "value" => $output
                    ]]];

                $shopify->Order($order_id)->Transaction->post($transaction_data);
                $shopify->Order($order_id)->put($installment_details);
                \DB::table('shopify_excel_upload')->where('_id', $_id)->update([$installment_index => 'Yes']);
            }
        }
    }
}