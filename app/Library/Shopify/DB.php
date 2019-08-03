<?php

namespace App\Library\Shopify;

use App\Models\ShopifyExcelUpload;
use App\Models\Product;
use App\Library\Shopify\API;
use App\User;

class DB
{
	/**
	 * @param $activity_id
	 * @param $activity_fee
	 *
	 * @return int
	 */
	public static function get_variant_id($activity_id) {
	    $product =  Product::ActiveProduct()->where('variants.sku', $activity_id)->firstOrFail(['variants.id']);
		return (string) $product['variants'][0]['id'];
	}

	public static function is_activity_duplicate($activity_id) {
	    $product =  Product::where('domain_store', env('SHOPIFY_STORE'))->where('variants.sku', $activity_id)->get();
	    if(sizeof($product) > 1) {
	        return true;
	    }
	    
	    return false;
	}
	
	public static function check_activity_fee_value($activity_fee, $activity_id) {
		$product =  Product::ActiveProduct()->where('variants.sku', $activity_id)->firstOrFail();
		
		foreach($product['variants'] as $variant) {
			if($variant['price'] == $activity_fee){
				return true;
			}
		}

		return false;
	}


    public static function check_inventory_status($variant_id){
    	$product = Product::where('variants.id',$variant_id)->first(['variants.inventory_management','variants.inventory_quantity']);
    	if(!empty($product['variants'][0]['inventory_management'])){
    		if($product['variants'][0]['inventory_quantity'] <= 0){
    			return false;
    		}
    	}
    	return true;
    }
	/**
	 * @param $object_id
	 * @param $shopify_order_id
	 *
	 * @return mixed
	 */
	public static function update_order_id_in_upload($object_id, $shopify_order_id) {
		return ShopifyExcelUpload::where('_id', $object_id)->update(['order_id'=> $shopify_order_id]);
	}

	/**
	 * @param $_id Object ID - Primary key
	 * @param int $number of installment store in database
	 *
	 * @return mixed
	 */
	public static function mark_installment_status_processed($_id, $number) {
		$installment_index = sprintf("payments.%s.processed", $number);
		$order_update_node = sprintf("payments.%s.order_update_at", $number);

		return ShopifyExcelUpload::find($_id)->update([$installment_index => 'Yes', $order_update_node => time()]);
	}

	public static function populate_error_in_payments_array($_id,$number,$error){

		$installment_index = sprintf("payments.%s.errors", $number);

		return ShopifyExcelUpload::find($_id)->update([$installment_index => $error]);
	}

	/**
	 * @param $_id Object ID - Primary key
	 *
	 * @return mixed
	 */
	public static function mark_status_completed($_id) {
		$Document = ShopifyExcelUpload::find($_id);
		if (!$Document) {
			return;
		}

		$allProcessed = true;
		foreach ($Document->payments as $payment) {
			if (strtolower($payment['processed']) !=  'yes') {
				$allProcessed = false;
				break;
			}
		}

		if ($allProcessed) {
			$Document->update(['job_status' => ShopifyExcelUpload::JOB_STATUS_COMPLETED]);
		}

		return $Document;
	}

	/**
	 * @param $_id Object ID - Primary key
	 * @param array $error
	 *
	 * @return mixed
	 */
	public static function mark_status_failed($_id, array $error = []) {
		return ShopifyExcelUpload::find($_id)->update(['job_status' => ShopifyExcelUpload::JOB_STATUS_FAILED, 'errors' => $error]);
	}

    /**
     * @param $object_id
     * @param $shopify_customer_id
     * @return mixed
     */
	public static function update_customer_id_in_upload($object_id, $shopify_customer_id){
	    return ShopifyExcelUpload::find($object_id)->update(['customer_id' => $shopify_customer_id]);
    }
    
    # Not used
    // public static function check_shopify_activity_id_in_database($product_sku){
    // 	return \DB::table('shopify_products')->where('variants.sku', $product_sku)->exists();
    // }

    public static function shopify_product_database_exists($product_sku) {
    	return Product::ActiveProduct()->where('variants.sku', $product_sku)->exists();
	}

    public static function check_product_existence_in_database($product_id){
    	return Product::where('id', $product_id)->exists();
    }

    # Not used
    // public static function check_customer_existence_in_database($customer_id){
    // 	return \DB::table('shopify_customers')->where('id',$customer_id)->exists();
    // }

    public static function get_user_email_id_from_database($id){
    	return User::findOrFail($id)['email'];
    }

    public static function check_if_already_used($cheque_no, $micr_code = 0, $account_no = 0,$payment_index, $activity_id, $enrollment_date, $enrollment_no) {
		$ORM = ShopifyExcelUpload::where('payments.chequedd_no', $cheque_no);
		
		if (!empty($micr_code)) {
			$ORM->where('payments.micr_code', $micr_code);
		}

		if (!empty($account_no)) {
			$ORM->where('payments.drawee_account_number', $account_no);
		}

		if($ORM->exists()) {
			$document = $ORM->first(['payments','date_of_enrollment','shopify_activity_id','school_enrollment_no'])->toArray();

			if($document['date_of_enrollment'] == $enrollment_date && $document['shopify_activity_id'] == $activity_id && $document['school_enrollment_no'] == $enrollment_no){

				foreach($document['payments'] as $index => $payment){
					if($payment['chequedd_no'] == $cheque_no && $payment['micr_code'] == $micr_code && $payment['drawee_account_number'] == $account_no && $index == $payment_index){
						return false;
					}
				}
			}
			return true;
		}

    	return false;
    }
   
    /**
     * Not in Use
     * @ignore
     */
    public static function sync_all_products_from_shopify() {
    	$ShopifyAPI = new API();
    	$page = 1;
    	$hasProducts = true;
    	while($hasProducts) {
        	$params = ['limit' => 5,'page'=> $page];
        	$products = $ShopifyAPI->GetProducts($params);
        	
        	if (!count($products)) {
        		$hasProducts = false;
        	} else {
        		foreach($products as $product){
        			if(!DB::check_product_existence_in_database($product["id"])){
    					\DB::table('shopify_products')->insert($product);	    		
        			}
        		}
    		}
       		$page++;
	   }
	}

	/**
	 * Not in Use
	 * @ignore
	 */
	public static function sync_all_customers_from_shopify() {
		$ShopifyAPI = new API();
    	$page = 1;
    	$hasCustomers = true;
    	while($hasCustomers) {
	    	$params = ['page'=> $page];
	    	$customers = $ShopifyAPI->GetCustomers($params);

	    	if (!count($customers)) {
	    		$hasCustomers = false;
	    	} else {
	    		foreach($customers as $customer){
	    			if(!DB::check_customer_existence_in_database($customer["id"])){
						\DB::table('shopify_customers')->insert($customer);	    		
	    			}
	    		}
			}
		    $page++;
   	   }
   }

}