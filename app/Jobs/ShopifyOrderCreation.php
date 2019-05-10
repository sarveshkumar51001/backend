<?php

namespace App\Jobs;

use App\Library\Shopify\DB;
use App\Library\Shopify\API;
use App\Library\Shopify\DataRaw;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class ShopifyOrderCreation implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $data;

    public function __construct($data) {
        $this->data = $data;
    }

    public function handle() {
        logger($this->data);
        try {
            $Data = new DataRaw($this->data);

	        // Process only if the status of object is pending
	        if (strtolower($Data->GetJobStatus()) != 'pending') {
	        	return;
	        }

            $ShopifyAPI = new API();
            $customer= $ShopifyAPI->SearchCustomer($Data->GetPhone(),$Data->GetEmail());
            $shopifyCustomerId = $customer[0]["id"];
            DB::update_customer_id_in_upload($Data->ID(),$shopifyCustomerId);

	        // If customer is not found then create a new customer first
	        if (empty($customer)) {
		        $new_customer= $ShopifyAPI->CreateCustomer($Data->GetCustomerCreateData());
		        $shopifyCustomerId = $new_customer["id"];
                DB::update_customer_id_in_upload($Data->ID(),$shopifyCustomerId);
	        }

            // Is it a new order?
	        $variantID = DB::get_variant_id($Data->GetActivityID());
	        $shopifyOrderId = $Data->GetOrderID();


	        // Is it a new order?
	        if (empty($Data->GetOrderID())) {

	        	if($Data->HasInstallment()){
	        		$status_installment = true;
	        	}
	        	else{
	        		$status_installment = false;
	        	}
				$order = $ShopifyAPI->CreateOrder($Data->GetOrderCreateData($variantID,$status_installment,$shopifyCustomerId));
		        $shopifyOrderId = $order['id'];
		        DB::update_order_id_in_upload($Data->ID(),$shopifyOrderId);
	        }

	        if ($Data->HasInstallment()) {
		        // Loop through all the installments in system for the order

		        //>>>>>>>Point of error<<<<<<<<<<//
		        foreach ($Data->GetInstallments() as $index => $installment) {
		
			        // Get the installment data in proper format
			   	    list($transaction_data, $installment_details) = DataRaw::GetInstallmentData($installment, $index);

			        // Shopify Update: Posting new transaction part of installments
			        $ShopifyAPI->PostTransaction($shopifyOrderId, $transaction_data);

			        // Shopify Update: Append transaction data in given order
			        $ShopifyAPI->UpdateOrder($shopifyOrderId, $installment_details);

			        // DB UPDATE: Mark the installment node as
			        DB::mark_installment_status_processed($Data->ID(), $index);
		        }
	        }
	        // Finally mark the object as process completed
	        DB::mark_status_completed($Data->ID());

        } catch(\Exception $e) {
        	DB::mark_status_failed($Data->ID());

        	logger($e);
	        dd($e);

            $this->fail($e);
        }
    }
}