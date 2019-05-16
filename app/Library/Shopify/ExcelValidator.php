<?php

namespace App\Library\Shopify;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use App\Library\Shopify\DataRaw;
use App\Library\Shopify\DB;

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
			$this->ValidateChequeDetails($data);
			}

		$info = $this->File->GetFormattedData();
		$this->ValidateChequeDetails($info[1]);

		$this->ValidateAmount();

		return $this->errors;
	}

	private function ValidateData(array $data) {

		$rules = [
			"shopify_activity_id" => "required|string|min:3",
			"school_name" => "required|string",
			"school_enrollment_no" => "required|string|min:4",
			"mobile_number" => "required|regex:/^[0-9]{10}$/",
			"email_id" => "email|regex:/^.+@.+$/i",
			"date_of_enrollment" => "required",
			"final_fee_incl_gst" => "numeric"
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
		$modeWiseTotal = $this->get_amount_total($this->File->GetFormattedData());

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
	 * @param $file
	 *
	 * @return array
	 */
	private function get_amount_total($file) {
		$installmentTotal = $cashTotal = $chequeTotal = $onlineTotal = 0;

		foreach ($file as $index => $row) {
			if (array_key_exists('installments', $row)) {
				// Sum up all the installment
				foreach ($row["installments"] as $installment) {
					// $installmentTotal += $installment['installment_amount'];
					// Sum up all the installment data
					$installmentMode = strtolower($installment["mode_of_payment"]);
					if ($installmentMode == 'cash') {
						$cashTotal += $installment["installment_amount"];
					} elseif ($installmentMode == 'cheque') {
						$chequeTotal += $installment["installment_amount"];
					} elseif($installmentMode == 'online') {
						$onlineTotal += $installment["installment_amount"];
					} else {
						$this->errors[] = "Invalid mode_of_payment [$installmentMode] received for row no " . ($index +1);
					}
				}
			}

			// If the order is without installments?
			else {
				$mode = strtolower($row["mode_of_payment"]);
				if ($mode == 'cash') {
					$cashTotal += $row["final_fee_incl_gst"];
				} elseif ($mode == 'cheque') {
					$chequeTotal += $row["final_fee_incl_gst"];
				} elseif($mode == 'online') {
					$onlineTotal += $row["final_fee_incl_gst"];
				} else {
					$this->errors[] = "Invalid mode_of_payment [$mode] received for row no " . ($index +1);
				}
			}
		}
		return [
			'cash_total' => $cashTotal,
			'cheque_total' => $chequeTotal,
			'online_total' => $onlineTotal
		];
	}
	private function ValidateChequeDetails(array $data){

		// Check if the order has installment
		if(!array_key_exists('installments',$data)){
			$cheque_no = $data['chequedd_no'];
			$account_no = $data['drawee_account_number'];
			$micr_code = $data['micr_code'];

			// Check if the combination of cheque no., micr_code and account_no. exists in database
			if(DB::check_cheque_details_existence($cheque_no,$micr_code,$account_no)){
				$this->errors[$data['sno']] = "Cheque Details entered already exists in database";
			}
		}
		else{
			// Looping through the installment data
			foreach ($data["installments"] as $index=>$installment){

				$cheque_no = $installment['cheque_no'];
				$micr_code  = $installment['micr_code'];
				$account_no = $installment['drawee_account_number'];

				// Looping through all the installments in the database
				for($i=1; $i <= env('INSTALLMENT_NUMBER'); $i++){
					if(DB::check_installment_cheque_details_existence($i,$cheque_no,$micr_code,$account_no)){
						$this->errors[$data['sno']] = "Cheque details for installment [index] already exists in database";
					}
				}
			}	
		}
	}
}