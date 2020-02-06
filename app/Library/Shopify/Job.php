<?php
namespace App\Library\Shopify;

use App\Models\ShopifyCustomer;
use App\Models\ShopifyExcelUpload;
use Exception;
use Carbon\Carbon;
use PHPShopify\Exception\ApiException;

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
     * @return array|void
     * @throws Exception
     */
    public static function run(DataRaw $Data)
    {
        // Process only if the status of object is pending
        if (strtolower($Data->GetJobStatus()) == ShopifyExcelUpload::JOB_STATUS_COMPLETED) {
            return;
        }

        $ShopifyAPI = new API();
        $variantID = DB::get_variant_id($Data->GetActivityID());

        if(empty($variantID)) {
            throw new \Exception("Product [" . $Data->GetActivityID() . "] is either disabled or does not exists.");
        }

        $ShopifyCustomer = [];
        $shopifyCustomerId = 0;
        $customers = [];

        // Searching customer on Shopify using API
        $ShopifyCustomers = $ShopifyAPI->SearchCustomer($Data->GetPhone(), $Data->GetEmail());

        // If customer found using Shopify API
        if(! empty($ShopifyCustomers)) {

            // Getting unique customer by checking phone or email id in customer data fetched from API
            $ShopifyCustomer = DB::get_customer($ShopifyCustomers, $Data->GetPhone(), $Data->GetEmail());

            // If unique customer found
            if (! empty($ShopifyCustomer)) {
                // Use the fetched unique customer for order creation
                $shopifyCustomerId = $ShopifyCustomer['id'];

                $CustomerUpdateData = $Data->GetCustomerUpdateData($ShopifyCustomer);

                $ShopifyCustomerUpdateData = [];

                // Checking if any field needs to be updated in Shopify
                foreach ($CustomerUpdateData as $key => $value) {
                    if($ShopifyCustomer[$key] != $value) {
                        $ShopifyCustomerUpdateData[$key] = $value;
                    }
                }

                // Update data in Shopify if not already updated
                if(!empty($ShopifyCustomerUpdateData)) {
                    $ShopifyAPI->UpdateCustomer($shopifyCustomerId, $ShopifyCustomerUpdateData);
                }
            }
        }

        // If unique customer not found using Shopify API then search in local DB
        if(empty($ShopifyCustomer)) {
            $ShopifyCustomer = DB::search_customer_in_database($Data->GetEmail(),$Data->GetPhone());

            if(! empty($ShopifyCustomer)) {
                $shopifyCustomerId = $ShopifyCustomer['id'];
            }
        }

        // Checking if shopify customer not found from Shopify and local DB
        // then create new customer in Shopify
        if(empty($ShopifyCustomer)) {
            $newShopifyCustomer = $ShopifyAPI->CreateCustomer($Data->GetCustomerCreateData());
            $shopifyCustomerId = $newShopifyCustomer['id'];

            // Create Customer in local DB if new customer is created in Shopify
            ShopifyCustomer::create($newShopifyCustomer);
        }

        // Check 3: Make sure by now we have customer id
        if (empty($shopifyCustomerId)) {
            throw new \Exception('Failed to get customer id from shopify data set');
        }

		// Update customer id in excel upload
		DB::update_customer_id_in_upload($Data->ID(), $shopifyCustomerId);

        $order = [];
		$shopifyOrderId = $Data->GetOrderID();

		// Is it a new order?
		if (empty($Data->GetOrderID())) {
            if (!DB::check_inventory_status($variantID)) {
                throw new \Exception("Product [" . $Data->GetActivityID() . "] is out of stock.");
            }
            if (!$Data->IsOnlinePayment()) {
                $order = $ShopifyAPI->CreateOrder($Data->GetOrderCreateData($variantID, $shopifyCustomerId));
                $shopifyOrderId = $order['id'];
                $shopifyOrderName = $order['name'];
                // Update order data in respective MongoDB document.
                DB::update_order_id_in_upload($Data->ID(), $shopifyOrderId, $shopifyOrderName);
            } else{
			    $order = $ShopifyAPI->CreateDraftOrder($Data->GetOrderCreateData($variantID,$shopifyCustomerId));
			    $shopifyOrderId = $order['id'];
			    $shopifyCheckoutUrl = $order['invoice_url'];
                DB::update_draft_order_data_in_upload($Data->ID(), $shopifyOrderId,$shopifyCheckoutUrl);
            }
			DB::update_order_data_in_upload($Data->ID(), $shopifyOrderId);
		}

		// Payment notes array
		$notes_array = DataRaw::GetPaymentDetails($Data->GetPaymentData());

		$previous_collected_amount = 0;
		$order_amount = 0;
		$collected_amount = 0;
		// Loop through all the installments in system for the order
		foreach ($Data->GetPaymentData() as $index => $installment) {

			//Getting previously collected amount for the order
			if(strtolower($installment['processed']) == 'yes'){
				$previous_collected_amount += $installment['amount'];
			}
            $payment_processed_date = $Data->GetPaymentProcessDate($installment);
            $transaction_data = DataRaw::GetTransactionData($installment, $payment_processed_date);

            if (empty($transaction_data) || (!empty($installment['chequedd_date']) && Carbon::createFromFormat(ShopifyExcelUpload::DATE_FORMAT, $installment['chequedd_date'])->timestamp > time())) {
                continue;
            }
            try {
                // Shopify Update: Posting new transaction part of installments
                $transaction_response = $ShopifyAPI->PostTransaction($shopifyOrderId, $transaction_data);

                if (!empty($transaction_response)) {

                    // ID of the transaction
                    $transaction_id = $transaction_response['id'];

                    // Adding current collected amount to previously collected amount
                    $order_amount += $installment['amount'];

                    // DB UPDATE: Mark the installment node as
                    DB::mark_installment_status_processed($Data->ID(),$transaction_id, $index);
                }
			} catch (ApiException $e) {
            	DB::populate_error_in_payments_array($Data->ID(), $index , $e->getMessage());
            	throw new ApiException($e->getMessage(),$e->getCode(),$e);
            }
		}
        $collected_amount = $order_amount + $previous_collected_amount;

		// Additional Order details
		$order_details = $Data->GetNotes($notes_array,$collected_amount);

		// Shopify Update: Append transaction data in given order for both draft and normal orders.
        if(!$Data->IsOnlinePayment()){
            $ShopifyAPI->UpdateOrder($shopifyOrderId, $order_details);
        } else{
            $ShopifyAPI->UpdateDraftOrder($shopifyOrderId,$order_details);
            DB::mark_status_due($Data->ID());
        }
        // Finally mark the object as process completed
        DB::mark_status_completed($Data->ID());

        return $order;
    }
}
