<?php

namespace App\Library\Shopify;

use App\Models\ShopifyExcelUpload;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Carbon\Carbon;

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
			$this->ValidateDate($data);
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
			"mobile_number" => "required|regex:^[6-9][0-9]{9}$^",
			"email_id" => "required|email",
			"date_of_enrollment" => "required",
			"activity_fee" => "required",
			"final_fee_incl_gst"=> "required|numeric",
			"branch" => ["required",Rule::in($valid_branch_names)],
			"activity" => "required",
			"payments.*.amount" => "numeric",
			"payments.*.chequedd_no" => "numeric",
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
		$PreviousCashTotal = $PreviousChequeTotal = $PreviousOnlineTotal = 0;

		foreach ($this->File->GetFormattedData() as $index => $row) {
	            // Get the primary combination to lookup in database
            $date_enroll = $row['date_of_enrollment'];
            $activity_id = $row['shopify_activity_id'];
            $std_enroll_no = $row['school_enrollment_no'];

        	$DatabaseRow = ShopifyExcelUpload::where('date_of_enrollment', $date_enroll)
                           ->where('shopify_activity_id', $activity_id)
                           ->where('school_enrollment_no', $std_enroll_no)
                           ->first();

	        if(!empty($DatabaseRow)) {
	        	foreach ($DatabaseRow['payments'] as $payment) {
	        		$paymentMode = strtolower($payment["mode_of_payment"]);
					if ( $paymentMode == strtolower(ShopifyExcelUpload::$modesTitle[ShopifyExcelUpload::MODE_CASH])) {
						$PreviousCashTotal += $payment["amount"];
					} elseif ( $paymentMode == strtolower(ShopifyExcelUpload::$modesTitle[ShopifyExcelUpload::MODE_CHEQUE]) || $paymentMode == strtolower(ShopifyExcelUpload::$modesTitle[ShopifyExcelUpload::MODE_DD]) ) {
						$PreviousChequeTotal += $payment["amount"];
					} elseif ( $paymentMode == strtolower(ShopifyExcelUpload::$modesTitle[ShopifyExcelUpload::MODE_ONLINE]) || $paymentMode == strtolower(ShopifyExcelUpload::$modesTitle[ShopifyExcelUpload::MODE_NEFT]) || $paymentMode == strtolower(ShopifyExcelUpload::$modesTitle[ShopifyExcelUpload::MODE_PAYTM]) ) {
						$PreviousOnlineTotal += $payment["amount"];
					}
	        	}
	        }
	        	              	
			foreach ($row['payments'] as $payment) {
				$paymentMode = strtolower( $payment["mode_of_payment"]);
				if ( $paymentMode == strtolower(ShopifyExcelUpload::$modesTitle[ShopifyExcelUpload::MODE_CASH])) {
					$cashTotal += $payment["amount"];
				} elseif ( $paymentMode == strtolower(ShopifyExcelUpload::$modesTitle[ShopifyExcelUpload::MODE_CHEQUE]) || $paymentMode == strtolower(ShopifyExcelUpload::$modesTitle[ShopifyExcelUpload::MODE_DD])) {
					$chequeTotal += $payment["amount"];
				} elseif ( $paymentMode == strtolower(ShopifyExcelUpload::$modesTitle[ShopifyExcelUpload::MODE_ONLINE])
				           || $paymentMode == strtolower(ShopifyExcelUpload::$modesTitle[ShopifyExcelUpload::MODE_PAYTM])
				           || $paymentMode == strtolower(ShopifyExcelUpload::$modesTitle[ShopifyExcelUpload::MODE_NEFT])) {
					$onlineTotal += $payment["amount"];
				} else {
					$this->errors[] = "Invalid mode_of_payment [$paymentMode] received for row no " . ($index + 1 );
				}
			}

			if(!empty($DatabaseRow) && ($PreviousCashTotal + $PreviousChequeTotal + $PreviousOnlineTotal) == 0) {
	            $this->errors[] = "Either same excel uploaded again or existing installments can't be modified.";	
			}
		}

		return [
			'cash_total' => $cashTotal - $PreviousCashTotal,
			'cheque_total' => $chequeTotal - $PreviousChequeTotal,
			'online_total' => $onlineTotal - $PreviousOnlineTotal
			];
	}

	 private function ValidateChequeDetails(array $data) {
		 foreach ($data['payments'] as $payment ) {
		 	$mode = strtolower($payment['mode_of_payment']);
		 	if ($mode == strtolower(ShopifyExcelUpload::$modesTitle[ShopifyExcelUpload::MODE_CHEQUE]) || $mode == strtolower(ShopifyExcelUpload::$modesTitle[ShopifyExcelUpload::MODE_DD])) {
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

	 		if($mode == strtolower(ShopifyExcelUpload::$modesTitle[ShopifyExcelUpload::MODE_ONLINE]) || $mode == strtolower(ShopifyExcelUpload::$modesTitle[ShopifyExcelUpload::MODE_PAYTM]) || $mode == strtolower(ShopifyExcelUpload::$modesTitle[ShopifyExcelUpload::MODE_NEFT]))
	 		{
	 			if(empty($payment['txn_reference_number_only_in_case_of_paytm_or_online'])){
	 				$this->errors['transaction_error'] = "Row Number- ".$data['sno']." Transaction Reference No. is mandatory in case of online and Paytm transactions.";
	 			}
	 		}
	 		if($mode == strtolower(ShopifyExcelUpload::$modesTitle[ShopifyExcelUpload::MODE_CHEQUE]) || $mode == strtolower(ShopifyExcelUpload::$modesTitle[ShopifyExcelUpload::MODE_DD])){
	 			if(empty($payment['chequedd_date']) || empty($payment['chequedd_no']) || empty($payment['micr_code']) || empty($payment['drawee_account_number'])){
	 				$this->errors['cheque_details_error'] = "Row Number- ".$data['sno']." Cheque Details are mandatory for transactions having payment mode as cheque.";
	 			}
	 		}
	 		if($amount > $data['final_fee_incl_gst']){
	 			$this->errors['amount_error'] = "Row Number- ".$data['sno']." Amount captured as payment is more than the final value of the order.";
	 		}
	 	}

	 	if(strstr($data['school_name'], ShopifyExcelUpload::SCHOOL_TITLE) && strtolower($data['external_internal']) == ShopifyExcelUpload::EXTERNAL_ORDER){
	 		$this->errors['internal_type_error'] = "Row Number- ".$data['sno']." The order type should be external in case of schools outside Apeejay.";
	 	}
	 	elseif(!strstr($data['school_name'], ShopifyExcelUpload::SCHOOL_TITLE) && strtolower($data['external_internal']) == ShopifyExcelUpload::INTERNAL_ORDER){
	 		$this->errors['external_type_error'] = "Row Number- ".$data['sno']." The order type should be internal for schools under Apeejay Education Society.";
	 	}
	}

	private function ValidateDate(array $data){

		if(Carbon::createFromFormat(ShopifyExcelUpload::DATE_FORMAT, $data['date_of_enrollment']) == false || strlen(Carbon::createFromFormat(ShopifyExcelUpload::DATE_FORMAT, $data['date_of_enrollment'])->year) == ShopifyExcelUpload::YEAR_COUNT) {
   			$this->errors['date_error'] = "Row Number- ".$data['sno']." The enrollment date is not in correct format. For eg.The correct format is 17/06/2019";
		}

		foreach($data['payments'] as $index => $payment){

			if(!empty($payment['chequedd_date']) && Carbon::createFromFormat(ShopifyExcelUpload::DATE_FORMAT, $payment['chequedd_date']) == false || strlen(Carbon::createFromFormat(ShopifyExcelUpload::DATE_FORMAT, $data['date_of_enrollment'])->year) == ShopifyExcelUpload::YEAR_COUNT){
   				$this->errors['cheque_date_error'] = "Row Number- ".$data['sno']." Incorrect format of cheque date in payment no. ".($index + 1)."The correct format is 17/06/2019";
			}
		}
	}
}