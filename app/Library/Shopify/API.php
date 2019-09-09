<?php

namespace App\Library\Shopify;

use PHPShopify\ShopifySDK;

/**
 * Class API
 * @package App\Shopify
 */
class API
{
	/**
	 * @var ShopifySDK
	 */
	public $Shopify;

	/**
	 * API constructor.
	 */
	public function __construct() {
		$this->Shopify = ShopifySDK::config(self::GetConfig());
	}

	public static function GetConfig() {
		return [
			'ShopUrl' => env('SHOPIFY_STORE'),
			'ApiKey' => env('SHOPIFY_APIKEY'),
			'Password' => env('SHOPIFY_PASSWORD')
		];
	}

	private function delay_request() {
	    sleep(1);
	}
	/**
	 * @param $data
	 *
	 * @return array
	 */
	public function CreateCustomer($data) {
	    $this->delay_request();
		return $this->Shopify->Customer->post($data);
	}

	/**
	 * @param $phone
	 * @param $email
	 *
	 * @return array
	 */
	public function SearchCustomer($phone,$email) {
	    $this->delay_request();
		$query = sprintf("phone:%s OR email:%s",$phone,$email);
		return $this->Shopify->Customer->search($query);
	}

	public function UpdateCustomer($customer_id, array $data) {
	    if(empty($data))
	        return false;
	    
		$this->delay_request();
		return $this->Shopify->Customer($customer_id)->put($data);
	}

	/**
	 * @param array $data
	 *
	 * @return array
	 */
	public function CreateOrder(array $data) {
	    $this->delay_request();
		return $this->Shopify->Order->post($data);
	}

	public function CreateDraftOrder(array $data){
	    $this->delay_request();
	    return $this->Shopify->DraftOrder->post($data);
    }

	/**
	 * @param int $orderID
	 * @param array $data
	 *
	 * @return array
	 */
	public function PostTransaction($orderID, array $data) {
	    $this->delay_request();
		return $this->Shopify->Order($orderID)->Transaction->post($data);
	}

	/**
	 * @param int $orderID
	 * @param array $data
	 *
	 * @return array
	 */
	public function UpdateOrder($orderID, array $data) {
	    $this->delay_request();
		return $this->Shopify->Order($orderID)->put($data);
	}

	public function UpdateDraftOrder($orderID, array $data) {
	    $this->delay_request();
	    return $this->Shopify->DraftOrder($orderID)->put($data);
    }

	/**
	 * @param $params
	 *
	 * @return array
	 */
	public function GetProducts($params){
	    $this->delay_request();
		return $this->Shopify->Product()->get($params);
	}

	/**
	 * @return int
	 * @throws \PHPShopify\Exception\SdkException
	 */
	public function CountProducts(){
	    $this->delay_request();
		return $this->Shopify->Product()->count();
	}

	/**
	 * @param $params
	 *
	 * @return array
	 */
	public function GetCustomers($params){
	    $this->delay_request();
		return $this->Shopify->Customer()->get($params);
	}
}
