<?php
namespace App\Library\Shopify;

use App\Models\Customer;
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

        $ShopifyAPI = new API();
        $variantID = DB::get_variant_id($Data->GetActivityID());
        $customers = [];

        # Search customer in database and if not found search in shopify
        $DBcustomer = DB::search_customer_in_database($Data->GetEmail(),$Data->GetPhone());
        if(empty($DBcustomer)) {
            $customers = $ShopifyAPI->SearchCustomer($Data->GetPhone(), $Data->GetEmail());
        } else {
            $shopify_customer = head($DBcustomer);
            $shopifyCustomerId = $shopify_customer['id'];
        }

        # If no customer found in search from shopify then create the customer and add in database
        if(empty($shopifyCustomerId)) {
            if (empty($customers)) {
                $shopify_customer = $ShopifyAPI->CreateCustomer($Data->GetCustomerCreateData());
                $shopifyCustomerId = $shopify_customer['id'];
            } else {
                # Getting unique customer by checking phone or email id in customer data fetched from API
                $unique_customer = DB::get_customer($customers, $Data->GetPhone(), $Data->GetEmail());

                # If no unique customer found then create the customer else fetch the unique customer for order creation
                if (empty($unique_customer)) {
                    $shopify_customer = $ShopifyAPI->CreateCustomer($Data->GetCustomerCreateData());
                    $shopifyCustomerId = $shopify_customer['id'];
                } else {
                    $shopify_customer = head($unique_customer);
                    $shopifyCustomerId = $shopify_customer['id'];

                    $ShopifyAPI->UpdateCustomer($shopifyCustomerId, $Data->GetCustomerUpdateData($shopify_customer));
                }
            }
        }
        # Create document for the customer in database
        if(isset($shopify_customer)){
            Customer::create($shopify_customer);
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

            $transaction_data = DataRaw::GetTransactionData($installment);

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
}
