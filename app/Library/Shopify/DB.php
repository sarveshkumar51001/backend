<?php

namespace App\Library\Shopify;

class DB
{
	/**
	 * @param string $activity_id
	 *
	 * @return mixed
	 */
	public static function get_variant_id(string $activity_id) {
		$product =  \DB::table('valedra_products')->where('product_sku', $activity_id)->get()->first();

		return $product['product_id'] ?? 0;
	}

	/**
	 * @param $object_id
	 * @param $shopify_order_id
	 *
	 * @return mixed
	 */
	public static function update_order_id_in_upload($object_id, $shopify_order_id) {
		return \DB::table('shopify_excel_upload')->where('_id', $object_id)->update(['order_id'=> $shopify_order_id]);
	}

	/**
	 * @param $_id Object ID - Primary key
	 * @param int $number of installment store in database
	 *
	 * @return mixed
	 */
	public static function mark_installment_status_processed($_id, $number) {
		$installment_index = sprintf("installments.%s.processed", $number);

		return \DB::table('shopify_excel_upload')->where('_id', $_id)->update([$installment_index => 'Yes']);
	}

	/**
	 * @param $_id Object ID - Primary key
	 *
	 * @return mixed
	 */
	public static function mark_status_completed($_id) {
		return \DB::table('shopify_excel_upload')->where('_id', $_id)->update(['job_status' => 'completed']);
	}

	/**
	 * @param $_id Object ID - Primary key
	 *
	 * @return mixed
	 */
	public static function mark_status_failed($_id) {
		return \DB::table('shopify_excel_upload')->where('_id', $_id)->update(['job_status' => 'failed']);
	}
}