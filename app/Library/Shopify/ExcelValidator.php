<?php

namespace App\Library\Shopify;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use App\Models\ShopifyExcelUpload;


/**
 * Class ExcelValidator
 * @package App\Library\Shopify
 */
class ExcelValidator
{
	/**
	 * @var Excel
	 */
	protected $File;
	protected $errors = [];
	protected $info = [];
	protected $warnings = [];
	protected $customDataToValidate = [];

	public function __construct(Excel $File, $customValidateData = []) {
		$this->File                 = $File;
		$this->customDataToValidate = $customValidateData;
	}

	/**
	 * @return array
	 * @throws \Exception
	 */
	public function Validate() {
		if (!$this->HasAllValidHeaders()) {
			$this->errors['incorrect_headers'] = 'Few headers are incorrect, download the latest sample format';

			return $this->errors;
		}

		foreach ($this->File->GetFormattedData() as $data) {
			$this->ValidateData($data);
			$this->ValidateFieldValues($data);
		}

		$this->ValidateAmount();

		foreach ($this->File->GetFormattedData() as $data) {
			$this->ValidateChequeDetails($data);
		}

		return $this->errors;
	}

	private function ValidateData(array $data) {

		$valid_branch_names = ['Faridabad 15','Charkhi Dadri','Faridabad 21 D','Sheikh Sarai International','Greater Kailash','Greater Noida','Mahavir Marg','Kharghar','Nerul','Noida','Pitampura','Rama Mandi','Saket','Sheikh Sarai']; 

		$rules = [
			"shopify_activity_id" => "required|string|min:3",
			"school_name" => "required|string",
			"school_enrollment_no" => "required|string|min:4",
			"mobile_number" => "required|regex:/^[0-9]{10}$/",
			"email_id" => "email|regex:/^.+@.+$/i",
			"date_of_enrollment" => "required|date_format:m-d-Y",
			"final_fee_incl_gst" => "numeric",
			"activity_fee" => "required",
			"final_fee_incl_gst"=> "required",
			"branch" => ["required",Rule::in($valid_branch_names)],
			"activity" => "required",
			"payments.*.amount" => "numeric",
			"payments.*.chequedd_no" => "numeric",
			"payments.*.chequedd_date" => "date_format:m-d-Y",
			"payments.*.drawee_name" => "string",
			"payments.*.drawee_account_number" => "numeric",
			"payments.*.micr_code" => "numeric",
			"external_internal" => "required"
		];

		$validator = Validator::make($data, $rules);	
		$errors = $validator->getMessageBag()->toArray();
		if (!empty($errors)) {
			$this->errors[$data['sno']] = $errors;
		}
	}

	private function ValidateAmount() {
		// Fetching collected amount in cash, cheque and online from request
		$amount_collected_cash   = $this->customDataToValidate["cash-total"];
		$amount_collected_cheque = $this->customDataToValidate["cheque-total"];
		$amount_collected_online = $this->customDataToValidate["online-total"];

		// Calling function for validating amount data
		$modeWiseTotal = $this->get_amount_total();

		if ($amount_collected_cash != $modeWiseTotal['cash_total']) {
			$this->errors['cash_total_mismatch'] = "Cash total mismatch, Entered total $amount_collected_cash, Sheet total " . $modeWiseTotal['cash_total'];
		}
		if ($amount_collected_cheque != $modeWiseTotal['cheque_total']) {
			$this->errors['cheque_total_mismatch'] = "Cheque total mismatch, Entered total $amount_collected_cash, Sheet total " . $modeWiseTotal['cheque_total'];
		}
		if ($amount_collected_online != $modeWiseTotal['online_total']) {
			$this->errors['online_total_mismatch'] = "Online total mismatch, Entered total $amount_collected_cash, Sheet total " . $modeWiseTotal['online_total'];
		}
	}

	public function HasAllValidHeaders() {
		foreach ($this->File->GetFormattedHeader() as $header) {
			if (!isset(Excel::$headerMap[$header])) {
				return false;
			}
		}

		return true;
	}

	/**
	 * @return array
	 */
	private function get_amount_total() {
		$cashTotal = $chequeTotal = $onlineTotal = 0;
		foreach ($this->File->GetFormattedData() as $index => $row) {
			foreach ($row['payments'] as $payment ) {
				$paymentMode = strtolower( $payment["mode_of_payment"] );
				if ( $paymentMode == 'cash' ) {
					$cashTotal += $payment["amount"];
				} elseif ( $paymentMode == 'cheque' ) {
					$chequeTotal += $payment["amount"];
				} elseif ( $paymentMode == 'online' ) {
					$onlineTotal += $payment["amount"];
				} else {
					$this->errors[] = "Invalid mode_of_payment [$paymentMode] received for row no " . ( $index + 1 );
				}
			}
		}

		return [
			'cash_total' => $cashTotal,
			'cheque_total' => $chequeTotal,
			'online_total' => $onlineTotal
		];
	}

	 private function ValidateChequeDetails(array $data) {
		 foreach ($data['payments'] as $payment ) {
		 	$mode = strtolower($payment['mode_of_payment']);
		 	if ($mode == 'cheque' || $mode == 'dd') {
			    $cheque_no = $payment['chequedd_no'];
			    $account_no = $payment['drawee_account_number'];
			    $micr_code = $payment['micr_code'];

			    // Check if the combination of cheque no., micr_code and account_no. exists in database
			    if(DB::check_if_already_used($cheque_no, $micr_code, $account_no)){
				    $this->errors[$data['sno']] = "Cheque/DD Details already used before.";
			    }
		    }
		 }
	 }

	 private function ValidateFieldValues(array $data){

	 	foreach($data['payments'] as $index => $payment){
			$mode = strtolower($payment['mode_of_payment']);
	 		$amount = $payment['amount'];

	 		if($mode == strtolower(ShopifyExcelUpload::$modesTitle[ShopifyExcelUpload::MODE_ONLINE])){
	 			if(empty($payment['txn_reference_number_only_in_case_of_paytm_or_online'])){
	 				$this->errors['Transaction Error'] = "Transaction Reference No. is mandatory in case of online and Paytm transactions.";
	 			}
	 		}
	 		if($mode == strtolower(ShopifyExcelUpload::$modesTitle[ShopifyExcelUpload::MODE_CHEQUE])){
	 			if(empty($payment['chequedd_date']) || empty($payment['chequedd_no']) || empty($payment['micr_code']) || empty($payment['drawee_account_number'])){
	 				$this->errors['Cheque Details Error'] = "Cheque Details are mandatory for transactions having payment mode as cheque.";
	 			}
	 		}
	 		if($amount > $data['final_fee_incl_gst']){
	 			$this->errors['Amount Error'] = "Amount captured as payment is more than the final value of the order.";
	 		}
	 	}
	 	if(!stripos($data['school_name'], ShopifyExcelUpload::SCHOOL_TITLE)){ 
			if(strtolower($data['external_internal']) == ShopifyExcelUpload::INTERNAL_ORDER){
	 		$this->errors['Type Error'] = "The order type should be external in case of schools outside Apeejay.";
	 		}
	 	}
	}
}