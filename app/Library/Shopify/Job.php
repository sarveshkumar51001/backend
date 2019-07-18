<?php

namespace App\Library\Shopify;

use App\Models\ShopifyExcelUpload;
use Exception;
use Carbon\Carbon;
use App\Jobs\ShopifyOrderCreation;

/**
 * Helper Job class
 * @package App\Library\Shopify
 */
class Job {

	/**
	 * @param DataRaw $Data
	 *
	 * @throws Exception
	 */
	public static function run(DataRaw $Data, $Job) {
		// Process only if the status of object is pending
		if (strtolower($Data->GetJobStatus()) != ShopifyExcelUpload::JOB_STATUS_PENDING || $Data->IsOnlinePayment()) {
			return;
		}

		// Check 1: check if correct activity id is given and exist in database
		$variantID = DB::get_variant_id($Data->GetActivityID(), $Data->GetActivityFee());

		if(empty($variantID)) {
			throw new Exception("Variant ID [".$Data->GetActivityID()."] with amount [".$Data->GetActivityFee()."] doesn't exists in database");
		}

		$ShopifyAPI = new API();
		$customer= $ShopifyAPI->SearchCustomer($Data->GetPhone(),$Data->GetEmail());

		// Check 2: Make sure there is only one customer with the given phone or email, otherwise fail
		if(sizeof($customer) > 1) {
			throw new Exception("More than one customer found with the email or mobile number provided.");
		}

		// If customer is not found then create a new customer first
		if (empty($customer)) {
			$new_customer = $ShopifyAPI->CreateCustomer($Data->GetCustomerCreateData());
			$shopifyCustomerId = $new_customer["id"];
		} else {
			$shopifyCustomerId = $customer[0]["id"];
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
			$order = $ShopifyAPI->CreateOrder($Data->GetOrderCreateData($variantID, $shopifyCustomerId));

			$shopifyOrderId = $order['id'];

			DB::update_order_id_in_upload($Data->ID(), $shopifyOrderId);
		}
		
		// Payment notes array
		$notes_array = DataRaw::GetPaymentDetails($Data->GetPaymentData());
		
		// Loop through all the installments in system for the order
		foreach ($Data->GetPaymentData() as $index => $installment) {
	
			$installmentData = DataRaw::GetInstallmentData($installment, $index, $notes_array);

			if (empty($installmentData)){
				continue;
			}

			if ( (!empty($installment['chequedd_date'])) && Carbon::createFromFormat(ShopifyExcelUpload::DATE_FORMAT,$installment['chequedd_date'])->timestamp > time()) {
				$object = ShopifyExcelUpload::find($Data->ID());
				$delay = Carbon::createFromFormat(ShopifyExcelUpload::DATE_FORMAT,$installment['chequedd_date'])->timestamp - time();
				// Dispatching new job with delay if PDC recorded
				ShopifyOrderCreation::dispatch($object)->delay(now()->addSeconds($delay)->addHours(13));
			}
			else{
			// Get the installment data in proper format
			list($transaction_data, $installment_details) = $installmentData;

			try{
			// Shopify Update: Posting new transaction part of installments
			$ShopifyAPI->PostTransaction($shopifyOrderId, $transaction_data);
			// Shopify Update: Append transaction data in given order
			$ShopifyAPI->UpdateOrder($shopifyOrderId, $installment_details);
			}
			catch(\Exception $e){
				// Catching error exception while posting a transaction 
				DB::populate_error_in_payments_array($Data->ID(),$index,[
        		'message' => $e->getMessage(),
		        'time' => time(),
		        'job_id' => $Job->getJobId()
	        ]);
			}

			// DB UPDATE: Mark the installment node as
			DB::mark_installment_status_processed($Data->ID(), $index);
			}
		}

		// Finally mark the object as process completed
		DB::mark_status_completed($Data->ID());
	}
}