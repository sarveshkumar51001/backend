<?php

namespace App\Library\Shopify;

class Excel
{
	private $rawHeader = [];
	private $rawData = [];
	private $formattedData = [];
	private $formattedHeader = [];
	private $append = [];

	public static $headerMap = [
		'sno' => 'Sno',
		'order_id' => 'Shopify Order ID',
		'job_status' => 'job_status',
		'upload_date' => 'Upload Date',
		'date_of_enrollment' => 'Date of enrollment',
		'shopify_activity_id' => 'Shopify Activity ID',
		'delivery_institution' => 'Delivery Institution',
		'branch' => 'Branch',
		'external_internal' => 'External/ Internal',
		'school_name' => 'School Name',
		'student_first_name' => 'Student first name',
		'student_last_name' => 'Student last name',
		'activity' => 'Activity',
		'school_enrollment_no' => 'School enrollment no',
		'class' => 'Class',
		'section' => 'Section',
		'parent_first_name' => 'Parent First Name',
		'parent_last_name' => 'Parent Last Name',
		'mobile_number' => 'Mobile number',
		'email_id' => 'Email id',
		'activity_fee' => 'Activity fee',
		'scholarship_discount' => 'Scholarship/ Discount',
		'after_discount_fee' => 'After Discount Fee',
		'final_fee_incl_gst' => 'Final fee (incl GST)',
		'amount' => 'Amount',
		'mode_of_payment' => 'Mode of payment',
		'txn_reference_number_only_in_case_of_paytm_or_online' => 'Txn Reference Number (only in case of Paytm or Online)',
		'chequedd_no' => 'Cheque/DD No',
		'micr_code' => 'MICR code',
		'chequedd_date' => 'Cheque/DD Date',
		'drawee_name' => 'Drawee name',
		'drawee_account_number' => 'Drawee Account Number',
		'bank_name' => 'Bank Name',
		'bank_branch' => 'Bank Branch',
		'paid' => 'PAID',
		'pdc_collected' => 'PDC COLLECTED',
		'pdc_to_be_collected' => 'PDC TO BE COLLECTED',
		'pdc_collectedpdc_to_be_collectedstatus' => 'PDC Collected/PDC to be collected(Status)',
		'payments' => 'Payments',
		'payment_type'=> 'Payment Type'
	];

	public function __construct(array $header, array $data, array $append = []) {
		$this->rawHeader = $header;
		$this->rawData   = $data;
		$this->append    = $append;

		$this->Format();
	}

	public function Format() {
		// Trim white spaces
		array_walk($this->rawHeader, 'trim');

		$this->rawData = array_map(function($row) {
			$data = [];
			foreach ($row as $key => $value) {
				$data[$key] = trim($value);
			}

			return $data;
		}, $this->rawData);

		foreach ($this->rawData as $data) {
			// Removing unwanted columns
			foreach ($data as $key => $value) {
				if (strpos($key, '_') === 0) {
					unset($data[$key]);
				}
			}

			// Checking for all fields empty
			if (array_filter($data)) {
				# Making chunk of installments from the flat array

				$offset_array = array(22, 33, 44, 55, 66, 77);
				$final_slice = [];
				$pattern = '/(.+)(_[\d]+)/i';
				foreach ($offset_array as $offset_value) {
					$slice = array_slice($data, $offset_value, 11);
					foreach ($slice as $key => $value) {
						$replacement = '${1}';
						$new_key = preg_replace($pattern, $replacement, $key);
						$new_slice[$new_key] = $value;
					}

					$new_slice['processed'] = 'No';
					$new_slice['type'] = '';

					array_push($final_slice, $new_slice);
				}

				$i = 0;
				$slice_array = [];
				foreach ($final_slice as $slice) {
					$slice_array[$i++] = $slice;
				}

				$data['payments'] = [];
				$data['payments'] = $slice_array;

				# Removing slugged with count keys from the array
				foreach ($data as $key => $value) {
					if (preg_match('/_[1-9]$/', $key)) {
						unset($data[$key]);
					}
				}

				# Removing unwanted keys
				$unwanted_keys = array('installment_amount', 'pdc_collectedpdc_to_be_collectedstatus', 'cheque_no', 'chequeinstallment_date', '0','amount','mode_of_payment','txn_reference_number_only_in_case_of_paytm_or_online','chequedd_no','micr_code','chequedd_date','drawee_name','drawee_account_number','bank_name','bank_branch');

				foreach ($unwanted_keys as $key) {
					unset($data[$key]);
				}

				$this->formattedHeader = array_merge($this->formattedHeader, array_keys($data));
				$this->formattedData[] = array_merge($data, $this->append);
			}

			$this->FormatInstallments();
		}
	}

	/**
	 * Prepare the installments keys as per required format
	 */
	private function FormatInstallments() {
		$hasInstallment = false;
		foreach ($this->formattedData as &$data) {
			if (isset($data['installments'])) {
				foreach ($data['installments'] as $index => $installment) {
					if(!empty($installment['installment_amount'])){
						$hasInstallment = true;
					} else {
						unset($data['installments'][$index]);
					}
				}
			}

			if (!$hasInstallment) {
				unset($data['installments']);
			}
		}
	}

	public function GetFormattedData() {
		return $this->formattedData;
	}

	public function GetFormattedHeader() {
		return $this->formattedHeader;
	}

	public function GetRawData() {
		return $this->rawData;
	}

	public function GetRawHeaders() {
		return $this->rawHeader;
	}
}