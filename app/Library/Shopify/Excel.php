<?php

namespace App\Library\Shopify;

use App\Models\ShopifyExcelUpload;
use App\Models\ExternalCustomer;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class Excel
{
	private $rawHeader = [];
	private $rawData = [];
	private $formattedData = [];
	private $formattedHeader = [];
	private $append = [];

	public static $headerMap = [
		'sno' => 'Sno',
		'order_id' => 'Shopify Order',
		'job_status' => 'job_status',
		'upload_date' => 'Upload Date',
		'date_of_enrollment' => 'Date of enrollment',
		'shopify_activity_id' => 'Shopify Activity ID',
		'delivery_institution' => 'Delivery Institution',
		'branch' => 'Branch',
		'external_internal' => 'External/ Internal',
		'school_name' => 'School Name',
        'student_school_location' => 'School Location',
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
		'type' => 'Type',
		'processed' => 'Processed'
	];

	public static $headerViewMap = [
		'order_id' => 'Shopify Order',
		'job_status' => 'Status',
		'upload_date' => 'Upload Date',
		'date_of_enrollment' => 'Date of enrollment',
		'shopify_activity_id' => 'Shopify Activity ID',
		'delivery_institution' => 'Delivery Institution',
		'branch' => 'Branch',
		'external_internal' => 'External/Internal',
		'school_name' => 'School',
        'student_school_location' => 'School Location',
		'student_first_name' => 'Student first name',
		'student_last_name' => 'Student last name',
		'activity' => 'Activity',
		'school_enrollment_no' => 'Enrollment no',
		'class' => 'Class',
		'section' => 'Section',
		'parent_first_name' => 'Parent First Name',
		'parent_last_name' => 'Parent Last Name',
		'mobile_number' => 'Mobile',
		'email_id' => 'Email',
		'activity_fee' => 'Activity fee',
		'scholarship_discount' => 'Scholarship/Discount',
		'after_discount_fee' => 'After Discount Fee',
		'final_fee_incl_gst' => 'Final fee (incl GST)',
		'payments' => 'Payments',
		'errors' => 'Errors/Messages',
		'file_id' => "File ID"
	];

	const DATE_FIELDS = [
	    'date_of_enrollment',
        'chequedd_date',
        'chequedd_date_1',
        'chequedd_date_2',
        'chequedd_date_3',
        'chequedd_date_4',
        'chequedd_date_5'
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
			    // Checking if column is of type date
			    if(in_array($key, self::DATE_FIELDS) && is_numeric($value)) {
                        $value = Date::excelToDateTimeObject($value)->format(ShopifyExcelUpload::DATE_FORMAT);
                }
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

				$installment = 1;
				foreach ($offset_array as $offset_value) {
					$slice = array_slice($data, $offset_value, 11);
					foreach ($slice as $key => $value) {
						$replacement = '${1}';
						$new_key = preg_replace($pattern, $replacement, $key);
						$new_slice[$new_key] = $value;
					}

					$new_slice['installment'] = $installment;
					$new_slice['processed'] = 'No';
					$new_slice['errors'] = "";
					$new_slice['upload_date'] = time();
                    $new_slice['is_pdc_payment'] = false;

					if ($offset_value == 22) {
					    $new_slice['type'] = ShopifyExcelUpload::TYPE_ONETIME;
					} else {
						$new_slice['type'] = ShopifyExcelUpload::TYPE_INSTALLMENT;
					}

					$final_slice[] = $new_slice;
					$installment++;
				}

				$data['payments'] = $final_slice;

				# Removing slugged with count keys from the array
				foreach ($data as $key => $value) {
					if (preg_match('/_[1-9]$/', $key)) {
						unset($data[$key]);
					}
				}

				# Removing unwanted keys
				$unwanted_keys = array('pdc_collectedpdc_to_be_collectedstatus', 'cheque_no', 'chequeinstallment_date', '0','amount','mode_of_payment','txn_reference_number_only_in_case_of_paytm_or_online','chequedd_no','micr_code','chequedd_date','drawee_name','drawee_account_number','bank_name','bank_branch');
				foreach ($unwanted_keys as $key) {
					unset($data[$key]);
				}

				$this->formattedHeader = array_merge($this->formattedHeader, array_keys($data));
				$this->formattedData[] = array_merge($data, $this->append);
			}
		}

		$this->FormatInstallments();

		//$this->populateEnrollmentIDForExternals();
	}

	/**
	 * Prepare the enrollmentID as per required format for external user
	 * HEY20-00002, REY20-000001, VAL20-000001
	 */
	private function populateEnrollmentIDForExternals()
    {
		foreach($this->formattedData as &$data) {

			//Checking if external/internal
			if( strtolower($data['external_internal']) == ShopifyExcelUpload::EXTERNAL_ORDER) {

                // Check the "email_id" or "phone number" are present in "external_customers" collection
                $ORM = ExternalCustomer::select();
                if (!empty($data['mobile_number'])) {
                    $ORM->where(ExternalCustomer::PHONE, $data['mobile_number']);
                }

                if (!empty($data['email_id'])) {
                    if (!empty($data['mobile_number'])) {
                        $ORM->orWhere(ExternalCustomer::EMAIL, $data['email_id']);
                    } else {
                        $ORM->where(ExternalCustomer::EMAIL, $data['email_id']);
                    }
                }

				// Check the "email_id" or "phone number" are present in "external_customers" collection
				$customer = $ORM->first();

				// If not present in collection then create new external customer ID
				if(!$customer instanceof ExternalCustomer)
				{
					// Assigning external enrollment Id for new welcome external customer.
					$data['school_enrollment_no'] = $this->createExternalEnrollmentID($data['delivery_institution'], $data['date_of_enrollment']);
				}
				else
				{
                    // Otherwise we have customer present in collection
                    $data['school_enrollment_no'] = $customer['school_enrollment_no'];
				}
			}
		}
	}

	public static function upsertExternalCustomer($data) {
        //Checking if external/internal
        if(strtolower($data['external_internal']) == ShopifyExcelUpload::EXTERNAL_ORDER) {

            // Check the "email_id" or "phone number" are present in "external_customers" collection
            $customer = ExternalCustomer::where('school_enrollment_no', $data['school_enrollment_no'])->first();

            //If not present in collection then create new external customer ID
            if(!$customer instanceof ExternalCustomer) {

                // Assigning external enrollment Id for new welcome external customer.
                $newCustomer = new ExternalCustomer();
                $newCustomer->email_id = $data['email_id'];
                $newCustomer->phone = $data['mobile_number'];
                $newCustomer->parent_first_name = $data['parent_first_name'];
                $newCustomer->parent_last_name = $data['parent_last_name'];
                $newCustomer->source_code = $data['delivery_institution'];
                $newCustomer->school_enrollment_no = $data['school_enrollment_no'];

                $student_list = [];
                $student['student_first_name'] = $data['student_first_name'];
                $student['student_last_name'] = $data['student_last_name'];
                $student['class'] = $data['class'];
                $student['section'] = $data['section'];
                $student['school_name'] = $data['school_name'];
                $student['school_location'] = $data['student_school_location'];

                $student_list[] = $student;
                $newCustomer->students = $student_list;

                $newCustomer->save();
            }

            // Otherwise we have customer present in collection
            else {
                $createStudentNode = true;
                foreach($customer['students'] as $student) {
                    // Checking same first name student is exist or not
                    if(strtolower($student['student_first_name']) == strtolower($data['student_first_name'])) {
                        $createStudentNode = false;
                        break;
                    }
                }

                // True => Add another student node, False => Nothing
                if($createStudentNode){
                    $newStudent['student_first_name'] = $data['student_first_name'];
                    $newStudent['student_last_name'] = $data['student_last_name'];
                    $newStudent['class'] = $data['class'];
                    $newStudent['section'] = $data['section'];
                    $newStudent['school_name'] = $data['school_name'];
                    $newStudent['school_location'] = $data['student_school_location'];

                    $customer->push('students', $newStudent);
                    $customer->save();
                }
            }
        }
    }

	/*
	 * Creating external enrollment Id for new welcome external cutomer.
	 */
	public static function createExternalEnrollmentID($delivery_institution, $date_of_enrollment) {
		$Id = ExternalCustomer::where('source_code', $delivery_institution)->count();

		if(strtolower($delivery_institution) == 'h&r') {
			return 'HEY'.substr($date_of_enrollment, -2).'-'.str_pad(++$Id, 5, '0', STR_PAD_LEFT);
		}
		else if(strtolower($delivery_institution) == 'reynott') {
			return 'REY'.substr($date_of_enrollment, -2).'-'.str_pad(++$Id, 5, '0', STR_PAD_LEFT);
		}

		return 'VAL'.substr($date_of_enrollment, -2).'-'.str_pad(++$Id, 5, '0', STR_PAD_LEFT);
	}

	/**
	 * Prepare the installments keys as per required format
	 */
	private function FormatInstallments() {
		foreach ($this->formattedData as &$data) {

			$hasInstallment = false;
			foreach ($data['payments'] as $index => $payment) {
				if(!empty($payment['amount'])) {
					if ($payment['type'] == ShopifyExcelUpload::TYPE_INSTALLMENT) {
						$hasInstallment = true;
					}
				} else {
					unset($data['payments'][$index]);
				}

			}

			$data['order_type']  = $hasInstallment ? ShopifyExcelUpload::TYPE_INSTALLMENT : ShopifyExcelUpload::TYPE_ONETIME;
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

	public function GetExcelHeaders() {
	    $excel_headers = [
	        'sno',
	        'date_of_enrollment',
	        'shopify_activity_id',
	        'delivery_institution',
	        'branch',
	        'external_internal',
	        'school_name',
	        'student_school_location',
	        'student_first_name',
	        'student_last_name',
	        'activity',
	        'school_enrollment_no',
	        'class',
	        'section',
	        'parent_first_name',
	        'parent_last_name',
	        'mobile_number',
	        'email_id',
	        'activity_fee',
	        'scholarship_discount',
	        'after_discount_fee',
	        'final_fee_incl_gst',
	        'amount',
	        'mode_of_payment',
	        'txn_reference_number_only_in_case_of_paytm_or_online',
	        'chequedd_no',
	        'micr_code',
	        'chequedd_date',
	        'drawee_name',
	        'drawee_account_number',
	        'bank_name',
	        'bank_branch',
	        'pdc_collectedpdc_to_be_collectedstatus',
	        'amount_1',
	        'txn_reference_number_only_in_case_of_paytm_or_online_1',
	        'pdc_collectedpdc_to_be_collectedstatus_1',
	        'mode_of_payment_1',
	        'chequedd_no_1',
	        'micr_code_1',
	        'chequedd_date_1',
	        'drawee_name_1',
	        'drawee_account_number_1',
	        'bank_name_1',
	        'bank_branch_1',
	        'amount_2',
	        'txn_reference_number_only_in_case_of_paytm_or_online_2',
	        'pdc_collectedpdc_to_be_collectedstatus_2',
	        'mode_of_payment_2',
	        'chequedd_no_2',
	        'micr_code_2',
	        'chequedd_date_2',
	        'drawee_name_2',
	        'drawee_account_number_2',
	        'bank_name_2',
	        'bank_branch_2',
	        'amount_3',
	        'txn_reference_number_only_in_case_of_paytm_or_online_3',
	        'mode_of_payment_3',
	        'pdc_collectedpdc_to_be_collectedstatus_3',
	        'chequedd_no_3',
	        'micr_code_3',
	        'chequedd_date_3',
	        'drawee_name_3',
	        'drawee_account_number_3',
	        'bank_name_3',
	        'bank_branch_3',
	        'amount_4',
	        'txn_reference_number_only_in_case_of_paytm_or_online_4',
	        'mode_of_payment_4',
	        'pdc_collectedpdc_to_be_collectedstatus_4',
	        'chequedd_no_4',
	        'micr_code_4',
	        'chequedd_date_4',
	        'drawee_name_4',
	        'drawee_account_number_4',
	        'bank_name_4',
	        'bank_branch_4',
	        'amount_5',
	        'txn_reference_number_only_in_case_of_paytm_or_online_5',
	        'mode_of_payment_5',
	        'pdc_collectedpdc_to_be_collectedstatus_5',
	        'chequedd_no_5',
	        'micr_code_5',
	        'chequedd_date_5',
	        'drawee_name_5',
	        'drawee_account_number_5',
	        'bank_name_5',
	        'bank_branch_5',
	        'paid',
	        'pdc_collected',
	        'pdc_to_be_collected'];

	    return $excel_headers;
	}
}
