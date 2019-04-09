<?php

namespace App\Shopify;

Class Shopify_POST
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

    public static function create_order($shopify,$order_info)
    {

        $order_data = [
            "email" => $order_info["email_id"],
            "line_items" => [[
                "sku" => $order_info["shopify_activity_id"],
                "discount" => $order_info["scholarship_discount"],
                "taxable" => true,
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
            ]]];

        $shopify->Order->post($order_data);
    }

    public static function create_order_with_installment($shopify,$order_info){

        $order_data = [
            "email" => $order_info["email_id"],
            "line_items" => [[
                "sku" => $order_info["shopify_activity_id"],
                "discount" => $order_info["scholarship_discount"],
                "taxable" => true,
                "note_attributes" => [[
                    "name" => "Payment Mode",
                    "value" => $order_info["installments"]["installment_1"]["mode_of_payment"]
                ], [
                    "name" => "Cheque/DD No.",
                    "value" => $order_info["installments"]["installment_1"]["cheque_no"]
                ], [
                    "name" => "Cheque/DD Date",
                    "value" => $order_info["installments"]["installment_1"]["chequedd_date"]
                ], [
                    "name" => "Online Transaction Reference Number",
                    "value" => $order_info["installments"]["installment_1"]["txn_reference_number_only_in_case_of_paytm_or_online"]
                ], [
                    "name" => "Drawee Name",
                    "value" => $order_info["installments"]["installment_1"]["drawee_name"]
                ], [
                    "name" => "Drawee Account Number",
                    "value" => $order_info["installments"]["installment_1"]["drawee_account_number"]
                ], [
                    "name" => "MICR Code",
                    "value" => $order_info["installments"]["installment_1"]["micr_code"]
                ], [
                    "name" => "Bank Name",
                    "value" => $order_info["installments"]["installment_1"]["bank_name"]

                ], [
                    "name" => "Branch Name",
                    "value" => $order_info["installments"]["installment_1"]["bank_branch"]
                ]]
            ]]];
        $shopify->Order->post($order_data);

    }
}
