<?php

namespace App\Library\Shopify;

use App\Models\ShopifyExcelUpload;

class DataRaw
{
	public $data = [];

	public static $validNoteAttributes = [
		'mode_of_payment', 'chequedd_no', 'micr_code', 'chequedd_date', 'drawee_name', 'drawee_account_number',
		'bank_name', 'bank_branch'
	];

	/**
	 * DataRaw constructor.
	 *
	 * @param array $data
	 *
	 * @throws \Exception
	 */
	public function __construct(array $data) {
		if (empty($data)) {
			throw new \Exception('Empty data given');
		}

		$this->data = $data;
	}

	/**
	 * @return array
	 */
	public function GetData() {
		return $this->data;
	}

	/**
	 * @return int|mixed
	 */
	public function ID() {
		return $this->data['_id'] ?? 0;
	}

	public function GetEmail() {
		return $this->data['email_id'] ?? '';
	}

	public function GetPhone() {
		return $this->data['mobile_number'] ?? '';
	}

	public function GetActivityID() {
		return $this->data['shopify_activity_id'] ?? '';
	}

	public function GetOrderID() {
		return $this->data['order_id'] ?? 0;
	}

	public function GetJobStatus() {
		return $this->data['job_status'] ?? '';
	}

	public function GetActivityFee(){
		return $this->data['activity_fee'] ?? 0;
	}

	public function HasInstallment() {
		return ($this->data['order_type'] == ShopifyExcelUpload::TYPE_INSTALLMENT);
	}

	/**
	 * @return array
	 */
	public function GetCustomerCreateData() {
		$customerData = [
			"first_name" => $this->data["student_first_name"],
			"last_name" => $this->data["student_last_name"],
			"email" => $this->data["email_id"],
			"phone" => (string) $this->data["mobile_number"],
			"verified_email" => true,
			"metafields" => [[
				"key" => "School Name",
				"value" => $this->data["school_name"],
				"value_type" => "string",
				"namespace" => "global"
			], [
				"key" => "Class",
				"value" => $this->data["class"],
				"value_type" => "integer",
				"namespace" => "global"
			], [
				"key" => "Section",
				"value" => $this->data["section"],
				"value_type" => "string",
				"namespace" => "global"
			], [
				"key" => "School Enrollment No.",
				"value" => $this->data["school_enrollment_no"],
				"value_type" => "string",
				"namespace" => "global"
			], [
				"key" => "Parent First Name",
				"value" => $this->data["parent_first_name"],
				"value_type" => "string",
				"namespace" => "global"
			], [
				"key" => "Parent Last Name",
				"value" => $this->data["parent_last_name"],
				"value_type" => "string",
				"namespace" => "global"]]
		];

		return $customerData;
	}

	/**
	 * @param int $productVariantID
	 * @param array $customer_id
	 *
	 * @return array
	 * @throws \Exception
	 */
	public function GetOrderCreateData($productVariantID, $customer_id) {
		if (empty($productVariantID)) {
			throw new \Exception('Empty product variant id given');
		}

		$order_data['line_items'] = [[
			"variant_id" => $productVariantID
		]];

		$order_data['customer'] = [
		    "id" => $customer_id
        ];

       if (strtolower($this->data['order_type'] == 'installment')){
       	$order_data['tags'] = "Installments,Backend-App";
       }
       else{
       	$order_data['tags'] = "Backend-App";
       }

		$order_data['transactions'] = [[
			"amount" => $this->data['final_fee_incl_gst'],
			"kind" => "authorization"
		]];

		$order_data["financial_status"] = "pending";

		return $order_data;

	}

	public function GetPaymentData() {
		return $this->data['payments'] ?? [];
	}

	/**
	 * @param array $installment
	 * @param int $number
	 *
	 * @return array
	 */
	public static function GetInstallmentData(array $installment, $number) {
		
		if (empty($installment) || strtolower($installment['processed']) == 'yes') {
			return [];
		}

		$note = '';

		foreach ($installment as $key => $value) {
			$key = strtolower($key);
			if (!empty($value) && in_array($key, self::$validNoteAttributes)) {
				$note .= Excel::$headerMap[$key] . ": $value | ";
			}
		}

		$transaction_data = [
			"kind"   => "capture",
			"amount" => $installment['amount']
		];

		$installment_details = [
			"note_attributes" => [
				[
					"name"  => sprintf( "Installment-%s", $number),
					"value" => rtrim($note, '| ')
				]
			]
		];

		return [$transaction_data, $installment_details];
	}
}

