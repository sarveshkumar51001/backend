<?php
namespace App\Library\Shopify;

use App\Models\ShopifyExcelUpload;
use Exception;
use Carbon\Carbon;
use PHPShopify\Exception\ApiException;
use Illuminate\Support\Arr;

/**
 * Helper Job class
 *
 * @package App\Library\Shopify
 */

class Job
{

    /**
     *
     * @param DataRaw $Data
     *
     * @throws Exception
     */
    public static function run(DataRaw $Data)
    {
        // Process only if the status of object is pending
        if (strtolower($Data->GetJobStatus()) == ShopifyExcelUpload::JOB_STATUS_COMPLETED || $Data->IsOnlinePayment()) {
            return;
        }

        $variantID = DB::get_variant_id($Data->GetActivityID());

        $ShopifyAPI = new API();
        $customers = $ShopifyAPI->SearchCustomer($Data->GetPhone(), $Data->GetEmail());

        if (empty($customers)) {
            $new_customer = $ShopifyAPI->CreateCustomer($Data->GetCustomerCreateData());
            $shopifyCustomerId = $new_customer['id'];
        } else {
            // Getting unique customer by checking phone or email id in customer
            $unique_customer = DB::get_customer($customers, $Data->GetPhone(), $Data->GetEmail());

            if (empty($unique_customer)) {
                $new_customer = $ShopifyAPI->CreateCustomer($Data->GetCustomerCreateData());
                $shopifyCustomerId = $new_customer['id'];
            } else {
                $shopifyCustomer = head($unique_customer);
                $shopifyCustomerId = $shopifyCustomer['id'];

                $ShopifyAPI->UpdateCustomer($shopifyCustomerId, $Data->GetCustomerUpdateData($shopifyCustomer));
            }
        }

        // Check 3: Make sure by now we have customer id
        if (empty($shopifyCustomerId)) {
            throw new \Exception('Failed to get customer id from shopify data set');
        }

        // Update customer id in excel upload
        DB::update_customer_id_in_upload($Data->ID(), $shopifyCustomerId);

        $shopifyOrderId = $Data->GetOrderID();

        // Is it a new order?
        if (empty($Data->GetOrderID())) {

            if (!DB::check_inventory_status($variantID)) {
                throw new \Exception("Product [" . $Data->GetActivityID() . "] is either out of stock or is disabled.");
            }
            $order = $ShopifyAPI->CreateOrder($Data->GetOrderCreateData($variantID, $shopifyCustomerId));

            $shopifyOrderId = $order['id'];

            DB::update_order_id_in_upload($Data->ID(), $shopifyOrderId);
        }

        // Payment notes array
        $notes_array = DataRaw::GetPaymentDetails($Data->GetPaymentData());

        $previous_collected_amount = 0;
        $order_amount = 0;
        $collected_amount = 0;
        // Loop through all the installments in system for the order
        foreach ($Data->GetPaymentData() as $index => $installment) {

            // Getting previously collected amount for the order
            if (strtolower($installment['processed']) == 'yes') {
                $previous_collected_amount += $installment['amount'];
            }

            $processed_date = (new Job)->Payment_Process_Date($Data->HasInstallment(),$installment,$Data->GetEnrollmentDate());
            $transaction_data = DataRaw::GetTransactionData($installment,$processed_date);

            if (empty($transaction_data) || (!empty($installment['chequedd_date']) && Carbon::createFromFormat(ShopifyExcelUpload::DATE_FORMAT, $installment['chequedd_date'])->timestamp > time())) {
                continue;
            }

            try {
                // Shopify Update: Posting new transaction part of installments
                $transaction_response = $ShopifyAPI->PostTransaction($shopifyOrderId, $transaction_data);

                if (!empty($transaction_response)) {

                    // Adding current collected amount to previously collected amount
                    $order_amount += $installment['amount'];

                    // DB UPDATE: Mark the installment node as
                    DB::mark_installment_status_processed($Data->ID(), $index);
                }
            } catch (ApiException $e) {
                DB::populate_error_in_payments_array($Data->ID(), $index, $e->getMessage());

                throw new ApiException($e->getMessage(), $e->getCode(), $e);
            }
        }
        $collected_amount = $order_amount + $previous_collected_amount;

        // Additional Order details
        $order_details = $Data->GetNotes($notes_array, $collected_amount);

        // Shopify Update: Append transaction data in given order
        $ShopifyAPI->UpdateOrder($shopifyOrderId, $order_details);

        // Finally mark the object as process completed
        DB::mark_status_completed($Data->ID());
    }

    private function Payment_Process_Date($type_installment,$installment,$enrollment_date){

        // Returning enrollment date as processed at date in case of one time with oe without cheque date
        if(!$type_installment){
            $process_date = processed_date_format($enrollment_date);
            return $process_date;
        }
        else{
            //Returning today's date in case it is later than the cheque date and vice versa.
            if(!empty($installment['chequedd_date'])){

                if(Carbon::createFromFormat(ShopifyExcelUpload::DATE_FORMAT,$installment['chequedd_date'])->timestamp < time()){
                    $process_date = Carbon::now()->toIso8601String();
                    return $process_date;
                }else{
                    $process_date = processed_date_format($installment['chequedd_date']);
                    return $process_date;
                }
            }
            else{
                //Returning today's date in case of installment order if no cheque date found.
                $process_date = Carbon::now()->toIso8601String();
                return $process_date;
            }
        }
    }
}
