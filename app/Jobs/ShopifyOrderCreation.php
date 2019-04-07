<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use PHPShopify;
use App\JobHistory;

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

        $email = $this->data["email_id"];
        $phone = $this->data["mobile_number"];

        $query = sprintf("email:%s OR phone:%s", $email, $phone);

        $customers = $shopify->Customer->search($query);

        if (empty($customers)) {
            $customer_data = [
                "first_name" => $this->data["student_first_name"],
                "last_name" => $this->data["student_last_name"],
                "email" => $this->data["email_id"],
                "phone" => (string)$this->data["mobile_number"],
                "verified_email" => true,
                "metafields" => [[
                    "key" => "School Name",
                    "value" => $this->data["school_name"],
                    "value_type" => "string",
                    "namespace" => "global"
                ], [
                    "key" => "Class",
                    "value" => $this->data["class"],
                    "value_type" => "integer",
                    "namespace" => "global"
                ], [
                    "key" => "Section",
                    "value" => $this->data["section"],
                    "value_type" => "string",
                    "namespace" => "global"
                ], [
                    "key" => "School Enrollment No.",
                    "value" => $this->data["school_enrollment_no"],
                    "value_type" => "string",
                    "namespace" => "global"
                ], [
                    "key" => "Parent First Name",
                    "value" => $this->data["parent_first_name"],
                    "value_type" => "string",
                    "namespace" => "global"
                ], [
                    "key" => "Parent Last Name",
                    "value" => $this->data["parent_last_name"],
                    "value_type" => "string",
                    "namespace" => "global"]]
            ];
            $shopify->Customer->post($customer_data);

            if (empty($data["installments"]["installment_1"])) {
                $order_data = [
                    "email" => $this->data["email_id"],
                    "line_items" => [[
                        "sku" => $this->data["shopify_activity_id"],
                        "discount" => $this->data["scholarship_discount"],
                        "taxable" => true,
                        "note_attributes" => [[
                            "name" => "Payment Mode",
                            "value" => $this->data["mode_of_payment"]
                        ], [
                            "name" => "Cheque/DD No.",
                            "value" => $this->data["chequedd_no"]
                        ], [
                            "name" => "Cheque/DD Date",
                            "value" => $this->data["chequedd_date"]
                        ], [
                            "name" => "Online Transaction Reference Number",
                            "value" => $this->data["txn_reference_number_only_in_case_of_paytm_or_online"]
                        ], [
                            "name" => "Drawee Name",
                            "value" => $this->data["drawee_name"]
                        ], [
                            "name" => "Drawee Account Number",
                            "value" => $this->data["drawee_account_number"]
                        ], [
                            "name" => "MICR Code",
                            "value" => $this->data["micr_code"]
                        ], [
                            "name" => "Bank Name",
                            "value" => $this->data["bank_name"]

                        ], [
                            "name" => "Branch Name",
                            "value" => $this->data["bank_branch"]
                        ]]
                    ]]];
                $shopify->Order->post($order_data);
            } elseif (!empty($data["installments"]["installment_1"])) {
                $order_data = [
                    "email" => $this->data["email_id"],
                    "line_items" => [[
                        "sku" => $this->data["shopify_activity_id"],
                        "discount" => $this->data["scholarship_discount"],
                        "taxable" => true,
                        "note_attributes" => [[
                            "name" => "Payment Mode",
                            "value" => $this->data["installments"]["installment_1"]["mode_of_payment"]
                        ], [
                            "name" => "Cheque/DD No.",
                            "value" => $this->data["installments"]["installment_1"]["cheque_no"]
                        ], [
                            "name" => "Cheque/DD Date",
                            "value" => $this->data["installments"]["installment_1"]["chequedd_date"]
                        ], [
                            "name" => "Online Transaction Reference Number",
                            "value" => $this->data["installments"]["installment_1"]["txn_reference_number_only_in_case_of_paytm_or_online"]
                        ], [
                            "name" => "Drawee Name",
                            "value" => $this->data["installments"]["installment_1"]["drawee_name"]
                        ], [
                            "name" => "Drawee Account Number",
                            "value" => $this->data["installments"]["installment_1"]["drawee_account_number"]
                        ], [
                            "name" => "MICR Code",
                            "value" => $this->data["installments"]["installment_1"]["micr_code"]
                        ], [
                            "name" => "Bank Name",
                            "value" => $this->data["installments"]["installment_1"]["bank_name"]

                        ], [
                            "name" => "Branch Name",
                            "value" => $this->data["installments"]["installment_1"]["bank_branch"]
                        ]]
                    ]]];
                $shopify->Order->post($order_data);
            }
        }

        if (!empty($customers) && empty($data["installments"]["installment_1"])) {
            $order_data = [
                "email" => $this->data["email_id"],
                "line_items" => [[
                    "sku" => $this->data["shopify_activity_id"],
                    "discount" => $this->data["scholarship_discount"],
                    "taxable" => true,
                    "note_attributes" => [[
                        "name" => "Payment Mode",
                        "value" => $this->data["mode_of_payment"]
                    ], [
                        "name" => "Cheque/DD No.",
                        "value" => $this->data["chequedd_no"]
                    ], [
                        "name" => "Cheque/DD Date",
                        "value" => $this->data["chequedd_date"]
                    ], [
                        "name" => "Online Transaction Reference Number",
                        "value" => $this->data["txn_reference_number_only_in_case_of_paytm_or_online"]
                    ], [
                        "name" => "Drawee Name",
                        "value" => $this->data["drawee_name"]
                    ], [
                        "name" => "Drawee Account Number",
                        "value" => $this->data["drawee_account_number"]
                    ], [
                        "name" => "MICR Code",
                        "value" => $this->data["micr_code"]
                    ], [
                        "name" => "Bank Name",
                        "value" => $this->data["bank_name"]

                    ], [
                        "name" => "Branch Name",
                        "value" => $this->data["bank_branch"]
                    ]]
                ]]];
            $shopify->Order->post($order_data);
        } elseif (!empty($data["installments"]["installment_1"])) {

            $order_data = [
                "email" => $this->data["email_id"],
                "line_items" => [[
                    "sku" => $this->data["shopify_activity_id"],
                    "discount" => $this->data["scholarship_discount"],
                    "taxable" => true,
                    "note_attributes" => [[
                        "name" => "Payment Mode",
                        "value" => $this->data["installments"]["installment_1"]["mode_of_payment"]
                    ], [
                        "name" => "Cheque/DD No.",
                        "value" => $this->data["installments"]["installment_1"]["cheque_no"]
                    ], [
                        "name" => "Cheque/DD Date",
                        "value" => $this->data["installments"]["installment_1"]["chequedd_date"]
                    ], [
                        "name" => "Online Transaction Reference Number",
                        "value" => $this->data["installments"]["installment_1"]["txn_reference_number_only_in_case_of_paytm_or_online"]
                    ], [
                        "name" => "Drawee Name",
                        "value" => $this->data["installments"]["installment_1"]["drawee_name"]
                    ], [
                        "name" => "Drawee Account Number",
                        "value" => $this->data["installments"]["installment_1"]["drawee_account_number"]
                    ], [
                        "name" => "MICR Code",
                        "value" => $this->data["installments"]["installment_1"]["micr_code"]
                    ], [
                        "name" => "Bank Name",
                        "value" => $this->data["installments"]["installment_1"]["bank_name"]

                    ], [
                        "name" => "Branch Name",
                        "value" => $this->data["installments"]["installment_1"]["bank_branch"]
                    ]]
                ]]];
            $shopify->Order->post($order_data);

        }

    }
}
