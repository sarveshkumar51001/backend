<?php

namespace App\Shopify;

use PHPShopify;



Class Shopify
{
    public static function check_customer_existence($shopify,$customer_info){

        $email = $customer_info["email_id"];
        $phone = $customer_info["mobile_number"];

        $query = sprintf("email:%s OR phone:%s", $email, $phone);

        $customers = $shopify->Customer->search($query);

        return $customers;

    }
    public static function create_customer($shopify,$customer_info)
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

    public static function create_order($shopify,$customer_info){







































    }
}
