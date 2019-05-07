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

	/**
	 * @param $data
	 *
	 * @return array
	 */
	public function CreateCustomer($data) {
		return $this->Shopify->Customer->post($data);
	}

	/**
	 * @param string $email
	 * @param string $phone
	 *
	 * @return array
	 */
	public function SearchCustomer($email = '', $phone = '') {
		$query = sprintf("email:%s OR phone:%s", $email,$phone);
		return $this->Shopify->Customer->search($query);
	}

	/**
	 * @param array $data
	 *
	 * @return array
	 */
	public function CreateOrder(array $data) {
		return $this->Shopify->Order->post($data);
	}

	/**
	 * @param int $orderID
	 * @param array $data
	 *
	 * @return array
	 */
	public function PostTransaction($orderID, array $data) {
		return $this->Shopify->Order($orderID)->Transaction->post($data);
	}

	/**
	 * @param int $orderID
	 * @param array $data
	 *
	 * @return array
	 */
	public function UpdateOrder($orderID, array $data) {
		return $this->Shopify->Order($orderID)->put($data);
	}
}