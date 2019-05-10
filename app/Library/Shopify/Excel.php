<?php

namespace App\Library\Shopify;

class Excel
{
	private $header = [];
	private $rawData = [];
	private $formattedData = [];
	private $append = [];

	public static $headerMap = [
		'sno' => 'Sno',
		'date_of_enrollment' => 'Date of enrollment'
	];

	public function __construct(array $header, array $data, array $append = []) {
		$this->header = $header;
		$this->rawData = $data;
		$this->append = $append;

		$this->Format();
	}

	public function Format() {
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

				$offset_array = array(32, 43, 54, 65, 76);
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
					array_push($final_slice, $new_slice);
				}

				$i = 1;
				$slice_array = [];
				foreach ($final_slice as $slice) {
					$slice_array[$i++] = $slice;
				}

				$data['installments'] = $slice_array;

				# Removing slugged with count keys from the array
				foreach ($data as $key => $value) {
					if (preg_match('/_[1-9]$/', $key)) {
						unset($data[$key]);
					}
				}

				# Removing unwanted keys
				$unwanted_keys = array('installment_amount', 'pdc_collectedpdc_to_be_collectedstatus', 'cheque_no', 'chequeinstallment_date', '0');
				foreach ($unwanted_keys as $keys) {
					unset($data[$keys]);
				}

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

	public function GetRawData() {
		return $this->rawData;
	}

	public function GetHeaders() {
		return $this->header;
	}
}