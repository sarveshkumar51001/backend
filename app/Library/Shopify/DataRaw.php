<?php

namespace App\Library\Shopify;

class DataRaw
{
	public $data = [];

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

	public function HasInstallment() {
		return array_key_exists('installments',$this->data);
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
	 * @param bool $isInstallment
	 * @param array $notes
	 *
	 * @return array
	 * @throws \Exception
	 */
	public function GetOrderCreateData($productVariantID, $isInstallment,$customer_id) {
		if (empty($productVariantID)) {
			throw new \Exception('Empty product variant id given');
		}

		$order_data = [];

		$order_data['line_items'] = [[
			"variant_id" => $productVariantID

		]];
		$order_data['customer'] = [
		    "id" => $customer_id
        ];

		if ($isInstallment == true) {
			$order_data['transactions'] = [[
				"amount" => $this->data['final_fee_incl_gst'],
				"kind" => "authorization"
			]];
			$order_data["financial_status"] = "pending";
		} else {
			$order_data['transaction'] = [[
				"kind" => "capture"
			]];
			$order_data['note_attributes'] = [
				[
					"name" => "Payment Mode",
					"value" => $this->data["mode_of_payment"]
				], [
					"name" => "Cheque/DD No.",
					"value" => $this->data["chequedd_no"]
				], [
					"name" => "Cheque/DD Date",
					"value" => $this->data["chequedd_date"]
				], [
					"name" => "Online Transaction Reference Number",
					"value" => $this->data["txn_reference_number_only_in_case_of_paytm_or_online"]
				], [
					"name" => "Drawee Name",
					"value" => $this->data["drawee_name"]
				], [
					"name" => "Drawee Account Number",
					"value" => $this->data["drawee_account_number"]
				], [
					"name" => "MICR Code",
					"value" => $this->data["micr_code"]
				], [
					"name" => "Bank Name",
					"value" => $this->data["bank_name"]
				], [
					"name" => "Branch Name",
					"value" => $this->data["bank_branch"]
				]
			];
		}
		return $order_data;
	}

	public function GetInstallments() {
		return $this->data['installments'] ?? [];
	}

/////// Checked ///////

	/**
	 * @param array $installment
	 * @param int $number
	 *
	 * @return array
	 */
	public static function GetInstallmentData(array $installment, $number) {
		if (empty($installment) || $installment['processed'] == 'Yes') {
			return [];
		}
		$output = implode(',', array_map(function ($v, $k) {
			return sprintf( "%s - %s\n", $k, $v );
		}, $installment, array_keys($installment)));

		$transaction_data = [
			"kind"   => "capture",
			"amount" => $installment['installment_amount']
		];

		$installment_details = [
			"note_attributes" => [
				[
					"name"  => sprintf( "Installment-%s", $number),
					"value" => $output
				]
			]
		];
		return [$transaction_data, $installment_details];
	}
}

