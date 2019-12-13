<?php
namespace App\Library\Shopify;

use App\Models\ShopifyExcelUpload;
use App\Models\Student;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Carbon\Carbon;

/**
 * Class ExcelValidator
 *
 * @package App\Library\Shopify
 */
class ExcelValidator
{

    /**
     *
     * @var Excel
     */
    protected $File;

    protected $errors = [];

    protected $info = [];

    protected $warnings = [];

    protected $customDataToValidate = [];

    public $FileFormattedData = [];

    protected $row_no = 0;

    public function __construct(Excel $File, $customValidateData = [])
    {
        $this->File = $File;
        $this->customDataToValidate = $customValidateData;
        $this->FileFormattedData = $this->File->GetFormattedData();
    }

    /**
     *
     * @return array
     * @throws \Exception
     */
    public function Validate()
    {
        $sheet_has_column_validation_error = false;
        if (! $this->HasAllValidHeaders()) {
            $this->errors['incorrect_headers'] = Errors::INCORRECT_HEADER_ERROR;
            return $this->errors;
        }

        if (count($this->FileFormattedData) < 1) {
            $this->errors['sheet']['empty_file'] = Errors::EMPTY_FILE_ERROR;
            return $this->errors;
        }

        // Finding data validation errors
        foreach ($this->FileFormattedData as $index => $data) {
            $this->row_no ++;

            $validation_error = $this->ValidateData($data);

            if ($this->ValidateDuplicateRow($data) || $validation_error) {
                if ($validation_error) {
                    $sheet_has_column_validation_error = true;
                }
                unset($this->FileFormattedData[$index]);
                continue;
            }
            $this->ValidateHigherEducationData($data);
            $this->ValidatePaymentDetails($data);
            $this->ValidateFieldValues($data);
            $this->ValidateActivityDetails($data);
        }

        if ($sheet_has_column_validation_error) {
            $this->errors['sheet']['priority_error'] = 'There are errors in sheets due to which collection cannot be calculated correctly. Please correct below errors and try again.';
        } else {
            $this->ValidateAmount();
        }

        return $this->errors;
    }

    private function ValidateDuplicateRow(array $row)
    {
        $is_duplicate = false;
        $date_enroll = $row['date_of_enrollment'];
        $activity_id = $row['shopify_activity_id'];
        $std_enroll_no = $row['school_enrollment_no'];

        $DatabaseRow = ShopifyExcelUpload::where('date_of_enrollment', $date_enroll)->where('shopify_activity_id', $activity_id)
            ->where('school_enrollment_no', $std_enroll_no)
            ->first();

        if (! empty($DatabaseRow)) {
            $is_duplicate = true;
            $fields_updated = [];

            $except_payment_and_metadata = ShopifyExcelUpload::METADATA_FIELDS;
            array_push($except_payment_and_metadata, 'payments');

            foreach (Arr::except($row, $except_payment_and_metadata) as $index => $value) {
                if ($value != $DatabaseRow[$index]) {
                    $fields_updated[] = $index;
                }
            }

            if (! empty($fields_updated)) {
                $this->errors['rows'][$this->row_no][] = sprintf(Errors::FIELD_UPDATED_ERROR, implode($fields_updated, ","));
            }

            // Existing payments array
            $existingpayments = $DatabaseRow["payments"];

            foreach ($row["payments"] as $payment_index => $payment) {

                if (array_diff_assoc(Arr::only($existingpayments[$payment_index], ShopifyExcelUpload::CHEQUE_DD_FIELDS), Arr::only($payment, ShopifyExcelUpload::CHEQUE_DD_FIELDS))) {
                    $is_duplicate = false;
                    if ($existingpayments[$payment_index]['processed'] == 'Yes') {
                        $this->errors['rows'][$this->row_no][] = sprintf(Errors::PROCESSED_INSTALLMENT_ERROR, $payment_index + 1);
                    }
                }
            }
        }

        if ($is_duplicate) {
            $this->errors['rows'][$this->row_no][] = Errors::DUPLICATE_ROW_ERROR;
        }

        return $is_duplicate;
    }

    private function ValidateData(array $data)
    {

        $rules = [
            // Activity Details
            "date_of_enrollment" => [
                "required",
                "regex:" . ShopifyExcelUpload::DATE_REGEX
            ],
            "shopify_activity_id" => "required|string|min:3",
            "delivery_institution" => "required",
            "branch" => [
                "required",
                Rule::in(ShopifyExcelUpload::getBranchNames())
            ],
            "external_internal" => "required",

            // Student Details
            "school_name" => "required|string",
            "student_school_location" => "required|string",
            "student_first_name" => "required",
            "activity" => "required",
            "school_enrollment_no" => "required|string|min:4",
            "class" => [
                "required",
                Rule::in(array_merge(Student::CLASS_LIST,Student::HIGHER_CLASS_LIST))],

            "section" => ["required",
                Rule::in(array_merge(Student::SECTION_LIST,Student::HIGHER_SECTION_LIST))],

            // Parent Details
            "parent_first_name" => "required",
            "mobile_number" => "regex:/^[6-9][0-9]{9}+$/|not_exponential",
            "email_id" => "email",

            // Fee Details
            "activity_fee" => "required",
            "scholarship_discount" => "numeric",
            "after_discount_fee" => "numeric|amount",
            "final_fee_incl_gst" => "required|numeric|amount",
            "amount" => "numeric|amount",

            // Registration/Booking Fee
            "payments.0.mode_of_payment" => [
                "required",
                Rule::in(ShopifyExcelUpload::payment_modes())
            ],
            "payments.0.amount" => "required|numeric|amount",

            // All Payments
            "payments" => "required",
            "payments.*.amount" => "numeric|amount",
            "payments.*.mode_of_payment" => [
                Rule::in(ShopifyExcelUpload::payment_modes())
            ],
            "payments.*.chequedd_no" => "numeric|not_exponential",
            "payments.*.micr_code" => "numeric|not_exponential",
            "payments.*.chequedd_date" => [
                "regex:" . ShopifyExcelUpload::DATE_REGEX
            ],
            "payments.*.drawee_name" => "string",
            "payments.*.drawee_account_number" => "numeric|not_exponential"
        ];

        $validator = Validator::make($data, $rules);
        $errors = $validator->getMessageBag()->toArray();

        if (! empty($errors)) {
            $this->errors['rows'][$this->row_no] = Arr::flatten(array_values($errors));
            return true;
        }
        return false;
    }

    private function ValidateAmount()
    {
        if (count($this->FileFormattedData) < 1)
            return;

        // Fetching collected amount in cash, cheque and online from request
        $amount_collected_cash = $this->customDataToValidate["cash-total"];
        $amount_collected_cheque = $this->customDataToValidate["cheque-total"];
        $amount_collected_online = $this->customDataToValidate["online-total"];

        // Calling function for validating amount data
        $modeWiseTotal = $this->get_amount_total();

        if ($amount_collected_cash != $modeWiseTotal['cash_total']) {
            $this->errors['sheet'][] = sprintf(Errors::CASH_TOTAL_MISMATCH, $amount_collected_cash, $modeWiseTotal['cash_total']);
        }
        if ($amount_collected_cheque != $modeWiseTotal['cheque_total']) {
            $this->errors['sheet'][] = sprintf(Errors::CHEQUE_TOTAL_MISMATCH, $amount_collected_cheque, $modeWiseTotal['cheque_total']);
        }
        if ($amount_collected_online != $modeWiseTotal['online_total']) {
            $this->errors['sheet'][] = sprintf(Errors::ONLINE_TOTAL_MISMATCH, $amount_collected_online, $modeWiseTotal['online_total']);
        }
    }

    public function HasAllValidHeaders()
    {
        $has_valid_header = false;
        if ($raw_headers = array_slice($this->File->GetRawHeaders(), 0, 91)) {

            foreach ($this->File->GetExcelHeaders() as $header) {
                if (! in_array($header, $raw_headers)) {
                    $has_valid_header = false;
                    break;
                } else {
                    $has_valid_header = true;
                }
            }
        }

        return $has_valid_header;
    }

    /**
     *
     * @return array
     */
    private function get_amount_total()
    {
        $cashTotal = $chequeTotal = $onlineTotal = 0;
        $PreviousCashTotal = $PreviousChequeTotal = $PreviousOnlineTotal = 0;

        foreach ($this->FileFormattedData as $row) {
            // Get the primary combination to lookup in database

            $date_enroll = $row['date_of_enrollment'];
            $activity_id = $row['shopify_activity_id'];
            $std_enroll_no = $row['school_enrollment_no'];

            $DatabaseRow = ShopifyExcelUpload::where('date_of_enrollment', $date_enroll)->where('shopify_activity_id', $activity_id)
                ->where('school_enrollment_no', $std_enroll_no)
                ->first();

            if (! empty($DatabaseRow)) {
                foreach ($DatabaseRow['payments'] as $payment) {
                    $paymentMode = strtolower($payment["mode_of_payment"]);
                    // Checking whether the payment has any payment mode //
                    if (! empty($paymentMode)) {
                        if ($paymentMode == strtolower(ShopifyExcelUpload::$modesTitle[ShopifyExcelUpload::MODE_CASH])) {
                            $PreviousCashTotal += $payment["amount"];
                        } elseif ($paymentMode == strtolower(ShopifyExcelUpload::$modesTitle[ShopifyExcelUpload::MODE_CHEQUE]) || $paymentMode == strtolower(ShopifyExcelUpload::$modesTitle[ShopifyExcelUpload::MODE_DD])) {
                            $PreviousChequeTotal += $payment["amount"];
                        } elseif ($paymentMode == strtolower(ShopifyExcelUpload::$modesTitle[ShopifyExcelUpload::MODE_ONLINE]) || $paymentMode == strtolower(ShopifyExcelUpload::$modesTitle[ShopifyExcelUpload::MODE_NEFT]) || $paymentMode == strtolower(ShopifyExcelUpload::$modesTitle[ShopifyExcelUpload::MODE_PAYTM])) {
                            $PreviousOnlineTotal += $payment["amount"];
                        }
                    }
                }
            }

            foreach ($row['payments'] as $payment) {
                $paymentMode = strtolower($payment["mode_of_payment"]);
                // Checking whether the payment has any payment mode //
                if (! empty($paymentMode)) {
                    if ($paymentMode == strtolower(ShopifyExcelUpload::$modesTitle[ShopifyExcelUpload::MODE_CASH])) {
                        $cashTotal += $payment["amount"];
                    } elseif ($paymentMode == strtolower(ShopifyExcelUpload::$modesTitle[ShopifyExcelUpload::MODE_CHEQUE]) || $paymentMode == strtolower(ShopifyExcelUpload::$modesTitle[ShopifyExcelUpload::MODE_DD])) {
                        $chequeTotal += $payment["amount"];
                    } elseif ($paymentMode == strtolower(ShopifyExcelUpload::$modesTitle[ShopifyExcelUpload::MODE_ONLINE]) || $paymentMode == strtolower(ShopifyExcelUpload::$modesTitle[ShopifyExcelUpload::MODE_PAYTM]) || $paymentMode == strtolower(ShopifyExcelUpload::$modesTitle[ShopifyExcelUpload::MODE_NEFT])) {
                        $onlineTotal += $payment["amount"];
                    }
                }
            }
        }

        return [
            'cash_total' => $cashTotal - $PreviousCashTotal,
            'cheque_total' => $chequeTotal - $PreviousChequeTotal,
            'online_total' => $onlineTotal - $PreviousOnlineTotal
        ];
    }

    private function ValidatePaymentDetails(array $data)
    {
        $amount = 0;
        $final_fee = $data['final_fee_incl_gst'];

        foreach ($data['payments'] as $payment_index => $payment) {
            $payment = Arr::except($payment, ShopifyExcelUpload::PAYMENT_METAFIELDS);

            if (empty($payment['amount'])) {
                $this->errors['rows'][$this->row_no][] = sprintf(Errors::EMPTY_AMOUNT_ERROR, $payment_index + 1);
                continue;
            }

            $mode = strtolower($payment['mode_of_payment']);
            $amount += $payment['amount'];

            $offline_modes = [
                ShopifyExcelUpload::$modesTitle[ShopifyExcelUpload::MODE_CHEQUE],
                ShopifyExcelUpload::$modesTitle[ShopifyExcelUpload::MODE_DD]
            ];

            $online_modes = [
                ShopifyExcelUpload::$modesTitle[ShopifyExcelUpload::MODE_PAYTM],
                ShopifyExcelUpload::$modesTitle[ShopifyExcelUpload::MODE_NEFT]
            ];

            $cheque_dd_fields = Arr::only($payment, ShopifyExcelUpload::CHEQUE_DD_FIELDS);
            $online_fields = Arr::only($payment, ShopifyExcelUpload::ONLINE_FIELDS);

            if (in_array($mode, array_map('strtolower', $offline_modes))) {
                // Checking for offline mode PAYMENTS
                if (array_contains_empty_value($cheque_dd_fields)) {
                    // Checking for blank cheque/dd details
                    $this->errors['rows'][$this->row_no][] = sprintf(Errors::CHEQUE_DD_DETAILS_ERROR, $payment_index + 1);
                } else {
                    if (DB::check_if_already_used($payment['chequedd_no'], $payment['micr_code'], $payment['drawee_account_number'], $payment_index, $data['shopify_activity_id'], $data['date_of_enrollment'], $data['school_enrollment_no'])) {
                        // Check if the combination of cheque no., micr_code and account_no. exists in database
                        $this->errors['rows'][$this->row_no][] = sprintf(Errors::CHEQUE_DETAILS_USED_ERROR, $payment_index + 1);
                    }
                }
            } else if (in_array($mode, array_map('strtolower', $online_modes))) {
                // Checking for online mode payments
                if (array_contains_empty_value($online_fields)) {
                    // Checking for blank online details
                    $this->errors['rows'][$this->row_no][] = sprintf(Errors::ONLINE_PAYMENT_ERROR, $payment_index + 1);
                }
            } else if ($mode == strtolower(ShopifyExcelUpload::$modesTitle[ShopifyExcelUpload::MODE_CASH])) {
                // Checking for cash mode payments
                if (! array_contains_empty_value($cheque_dd_fields) || ! array_contains_empty_value($online_fields)) {
                    // Cheque/DD/Online should be blank for cash payments
                    $this->errors['rows'][$this->row_no][] = sprintf(Errors::CASH_PAYMENT_ERROR, $payment_index + 1);
                }
            } else if ($mode == strtolower(ShopifyExcelUpload::$modesTitle[ShopifyExcelUpload::MODE_ONLINE])){
                $this->errors['rows'][$this->row_no][] = sprintf(Errors::ONLINE_NOT_SUPPORTED_ERROR,$payment_index + 1);
            }
            else if (! empty($mode)) {
                // Checking for invalid paymemt mode
                $this->errors['rows'][$this->row_no][] = sprintf(Errors::INVALID_MODE_ERROR, $mode, $payment_index + 1);
            }

            // Function for checking whether the combination of amount and date present for each installment.
            // The cheque date is being treated as the expected date of collection for the payment.
            if ($payment['type'] == ShopifyExcelUpload::TYPE_INSTALLMENT) {
                if (empty($payment['mode_of_payment'])) {
                    if (empty($payment['amount']) || empty($payment['chequedd_date'])) {
                        $this->errors['rows'][$this->row_no][] = sprintf(Errors::EXPECTED_DATE_AMOUNT_ERROR, $payment_index + 1);
                    } else {
                        if (Carbon::now()->diffInDays(Carbon::createFromFormat(ShopifyExcelUpload::DATE_FORMAT, $payment['chequedd_date']), false) > 0) {
                            $except_amount_date = [
                                'amount',
                                'chequedd_date'
                            ];
                            if (! array_contains_empty_value(Arr::except($cheque_dd_fields, $except_amount_date)) || ! array_contains_empty_value($online_fields)) {
                                $this->errors['rows'][$this->row_no][] = Errors::FUTURE_PAYMENT_CHEQUE_DETAILS_ERROR;
                            }
                        } else {
                            $this->errors['rows'][$this->row_no][] = sprintf(Errors::FUTURE_INSTALLMENT_DATE_ERROR, $payment_index + 1);
                        }
                    }
                }
            }
        }

        if ($amount != $final_fee) {
            $this->errors['rows'][$this->row_no][] = Errors::ORDER_AMOUNT_TOTAL_ERROR;
        }
    }

    private function ValidateFieldValues(array $data)
    {
        if (empty($data['mobile_number']) && empty($data['email_id'])) {
            $this->errors['rows'][$this->row_no][] = Errors::CONTACT_DETAILS_ERROR;
        }
        // Fetching location for the delivery institution and branch
        $location = ShopifyExcelUpload::getLocation($data['delivery_institution'], $data['branch']);

        // If location not found and order type is not external...
        if (!$location && strtolower($data['external_internal']) != ShopifyExcelUpload::EXTERNAL_ORDER ) {
            $this->errors['rows'][$this->row_no][] = Errors::OUTSIDE_APEEJAY_ERROR;
            $this->errors['rows'][$this->row_no][] = Errors::LOCATION_ERROR;
        }
        else {
            // If the location found doesn't corresponds to higher education institute
            if (!$location['is_higher_education']) {
                if (strstr($data['school_name'], ShopifyExcelUpload::SCHOOL_TITLE)) {
                    if (strtolower($data['external_internal']) != ShopifyExcelUpload::INTERNAL_ORDER || strtolower($data['delivery_institution']) != strtolower(ShopifyExcelUpload::SCHOOL_TITLE)) {
                        $this->errors['rows'][$this->row_no][] = Errors::INTERNAL_TYPE_ERROR;
                    }
                } else {
                    if (strtolower($data['external_internal']) != ShopifyExcelUpload::EXTERNAL_ORDER || strtolower($data['delivery_institution']) == strtolower(ShopifyExcelUpload::SCHOOL_TITLE)) {
                        $this->errors['rows'][$this->row_no][] = Errors::EXTERNAL_TYPE_ERROR;
                    }
                }
            } else{
                if(strtolower($data['external_internal']) != ShopifyExcelUpload::INTERNAL_ORDER || strtolower($data['delivery_institution']) != strtolower(ShopifyExcelUpload::SCHOOL_TITLE)){
                    $this->errors['rows'][$this->row_no][] = Errors::INSTITUTE_ERROR;
                }
            }
        }
    }

    private function ValidateActivityDetails(array $data)
    {
        $enrollment_date = $data['date_of_enrollment'];
        $enrollment_no = $data['school_enrollment_no'];
        $activity_id = $data['shopify_activity_id'];
        $activity_fee = $data['activity_fee'];
        $final_fee = $data['final_fee_incl_gst'];
        $scholarship_amount = $data['scholarship_discount'];

        if (! DB::shopify_product_database_exists($activity_id)) {
            $this->errors['rows'][$this->row_no][] = Errors::ACTIVITY_ID_ERROR;
        } else if (DB::is_activity_duplicate($activity_id)) {
            $this->errors['rows'][$this->row_no][] = sprintf(Errors::DUPLICATE_ACTIVITY_ERROR, $activity_id);
        } else if (! DB::check_activity_fee_value($activity_fee, $activity_id)) {
            $this->errors['rows'][$this->row_no][] = Errors::ACTIVITY_FEE_ERROR;
        } else if (! DB::check_order_created($enrollment_date, $activity_id, $enrollment_no)) {
            $variant_id = DB::get_variant_id($activity_id);
            if (! DB::check_inventory_status($variant_id)) {
                $this->errors['rows'][$this->row_no][] = Errors::OUT_OF_STOCK_ERROR;
            }
        }

        if (empty($scholarship_amount)) {
            if ($activity_fee != $final_fee) {
                $this->errors['rows'][$this->row_no][] = Errors::FINAL_FEE_ERROR;
            }
        } else {
            if ($final_fee != ($activity_fee - $scholarship_amount)) {
                $this->errors['rows'][$this->row_no][] = Errors::DISCOUNT_APPLICATION_ERROR;
            }
        }
    }
    public function ValidateHigherEducationData(array $data){

        $location_data = ShopifyExcelUpload::getLocation($data['delivery_institution'],$data['branch']);

        // Proceed only if $location data is returned;
        if($location_data){
            // Proceeding only if location corresponds to higher institute
            if($location_data['is_higher_education']){
                // Checking whether the section value is for higher institutes
                if(!in_array($data['class'],Student::HIGHER_CLASS_LIST)){
                    $this->errors['rows'][$this->row_no][] = Errors::INSTITUTE_CLASS_ERROR;
                }
                if(!in_array($data['section'],Student::HIGHER_SECTION_LIST)){
                    $this->errors['rows'][$this->row_no][] = Errors::INSTITUTE_SECTION_ERROR;
                }
            } else{
                if(in_array($data['class'],Student::HIGHER_CLASS_LIST)){
                    $this->errors['rows'][$this->row_no][] = Errors::SCHOOL_CLASS_ERROR;
                }
                if(in_array($data['section'],Student::HIGHER_SECTION_LIST)){
                    $this->errors['rows'][$this->row_no][] = Errors::SCHOOL_SECTION_ERROR;
                }
            }
        }
    }

    /**
     * @return array
     */
    public function get_errors()
    {
        return $this->errors;
    }

}
