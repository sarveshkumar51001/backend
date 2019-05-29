<?php

namespace App\Library\Shopify;

use App\Models\ShopifyExcelUpload;
use Exception;

/**
 * Helper Job class
 * @package App\Library\Shopify
 */
class Job {

	public static $validNoteAttributes = [
		'mode_of_payment', 'chequedd_no', 'micr_code', 'chequedd_date', 'drawee_name', 'drawee_account_number',
		'bank_name', 'bank_branch','txn_reference_number_only_in_case_of_paytm_or_online'
	];
	/**
	 * @param DataRaw $Data
	 *
	 * @throws Exception
	 */
	public static function run(DataRaw $Data) {
		// Process only if the status of object is pending
		if (strtolower($Data->GetJobStatus()) != ShopifyExcelUpload::JOB_STATUS_PENDING) {
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

		$notes_array = [];
		$note = "";
		foreach ($Data->GetPaymentData() as $index => $installment){
			if(!strtotime($installment['chequedd_date']) > time() || !in_array('chequedd_date',$installment)){
				foreach ($installment as $key => $value) {
				$key = strtolower($key);
				if (!empty($value) && in_array($key, self::$validNoteAttributes)) {
					$note = Excel::$headerMap[$key] . ": $value | ";
			}	
		}
		$notes_array[] = $note;
	}
}
		// Loop through all the installments in system for the order
		foreach ($Data->GetPaymentData() as $index => $installment) {
	
			$installmentData = DataRaw::GetInstallmentData($installment, $index, $notes_array);
			if (empty($installmentData) || (!empty($installment['chequedd_date']) && strtotime($installment['chequedd_date']) > time())) {
				continue;
			}
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
		        'job_id' => $this->job->getJobId()
	        ]);
			}

			// DB UPDATE: Mark the installment node as
			DB::mark_installment_status_processed($Data->ID(), $index);
		}

		// Finally mark the object as process completed
		DB::mark_status_completed($Data->ID());
	}
}