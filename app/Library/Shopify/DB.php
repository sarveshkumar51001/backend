<?php

namespace App\Library\Shopify;

use App\Models\ShopifyExcelUpload;
use App\Library\Shopify\API;

class DB
{
	/**
	 * @param $activity_id
	 * @param $activity_fee
	 *
	 * @return int
	 */
	public static function get_variant_id($activity_id, $activity_fee) {
		$product =  \DB::table('shopify_products')->where('variants.sku', $activity_id)->get()->first();
		foreach($product['variants'] as $variant){
			if($variant['price'] == $activity_fee){
				return $variant['id'];
			}
		}
		return 0;
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
    
    public static function check_shopify_activity_id_in_database($product_sku){
    	return \DB::table('shopify_products')->where('variants.sku', $product_sku)->exists();
    }
    public static function get_shopify_product_from_database($product_sku){
		return \DB::table('shopify_products')->where('variants.sku', $product_sku)->first();
	}
    public static function check_product_existence_in_database($product_id){
    	return \DB::table('shopify_products')->where('id', $product_id)->exists();
    }

    public static function check_customer_existence_in_database($customer_id){
    	return \DB::table('shopify_products')->where('id',$customer_id)->exists();
    }

    public static function check_if_already_used($cheque_no, $micr_code = 0, $account_no = 0){
		$ORM = ShopifyExcelUpload::where('chequedd_no', $cheque_no);

		if (!empty($micr_code)) {
			$ORM->where('micr_code', $micr_code);
		}

		if (!empty($account_no)) {
			$ORM->where('drawee_account_number', $account_no);
		}

    	return $ORM->exists();
    }

    public static function check_installment_cheque_details_existence($i,$cheque_no,$micr_code,$account_no){
    	$cheque_no_index = sprintf("payments.%s.cheque_no",$i);
    	$micr_code_index = sprintf("payments.%s.micr_code",$i);
    	$account_no_index = sprintf("payments.%s.drawee_account_number",$i);

    	return ShopifyExcelUpload::where($cheque_no_index, $cheque_no)
	                           ->where($micr_code_index, $micr_code)
	                           ->where($account_no_index, $account_no)
	                           ->exists();
    }
    
    public static function sync_all_products_from_shopify(){
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

	public static function sync_all_customers_from_shopify(){
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