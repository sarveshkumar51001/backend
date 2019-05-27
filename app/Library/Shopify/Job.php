<?php

namespace App\Library\Shopify;


use App\Models\ShopifyExcelUpload;
use Exception;

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

		// Loop through all the installments in system for the order
		foreach ($Data->GetPaymentData() as $index => $installment) {

			$installmentData = DataRaw::GetInstallmentData($installment, $index);
			if (empty($installmentData) || (!empty($installment['chequedd_date']) && strtotime($installment['chequedd_date']) > time())) {
				continue;
			}

			// Get the installment data in proper format
			list($transaction_data, $installment_details) = $installmentData;

			// Shopify Update: Posting new transaction part of installments
			$ShopifyAPI->PostTransaction($shopifyOrderId, $transaction_data);

			// Shopify Update: Append transaction data in given order
			$ShopifyAPI->UpdateOrder($shopifyOrderId, $installment_details);

			// DB UPDATE: Mark the installment node as
			DB::mark_installment_status_processed($Data->ID(), $index);
		}

		// Finally mark the object as process completed
		DB::mark_status_completed($Data->ID());
	}
}