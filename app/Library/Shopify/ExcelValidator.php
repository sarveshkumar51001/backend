<?php
namespace App\Library\Shopify;

use App\Models\ShopifyExcelUpload;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;

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

    protected $FileFormattedData = [];

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
        if (! $this->HasAllValidHeaders()) {
            $this->errors['incorrect_headers'] = 'Either few headers are incorrect or wrong sheet uploaded, to resolve download the latest sample format.';
            return $this->errors;
        }

        if (count($this->FileFormattedData) < 1) {
            $this->errors['sheet']['empty_file'] = 'No data was found in the uploaded file';
            return $this->errors;
        }

        // Finding data validation errors
        foreach ($this->FileFormattedData as $index => $data) {
            $this->row_no ++;
            if ($this->ValidateDuplicateRow($data)) {
                unset($this->FileFormattedData[$index]);
                continue;
            }

            $this->ValidateData($data);
            $this->ValidatePaymentDetails($data);
            $this->ValidateFieldValues($data);
            $this->ValidateActivityDetails($data);
        }

        $this->ValidateAmount();

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
                $this->errors['rows'][$this->row_no][] = "Only Payment data can be updated for an Order. Field(s) " . implode($fields_updated, ",") . " has been changed";
            }

            // Existing payments array
            $existingpayments = $DatabaseRow["payments"];

            foreach ($row["payments"] as $payment_index => $payment) {

                if (array_diff_assoc(Arr::only($existingpayments[$payment_index], ShopifyExcelUpload::CHEQUE_DD_FIELDS), Arr::only($payment, ShopifyExcelUpload::CHEQUE_DD_FIELDS))) {
                    $is_duplicate = false;
                    if ($existingpayments[$payment_index]['processed'] == 'Yes') {
                        $this->errors['rows'][$this->row_no][] = "Already Processed installments can't be modified. Installment " . ($payment_index + 1) . " have been modified";
                    }
                }
            }
        }

        if ($is_duplicate) {
            $this->errors['rows'][$this->row_no][] = "This order has already been processed";
        }

        return $is_duplicate;
    }

    private function ValidateData(array $data)
    {
        $valid_branch_names = [
            'Faridabad 15',
            'Charkhi Dadri',
            'Faridabad 21D',
            'Sheikh Sarai International',
            'Greater Kailash',
            'Greater Noida',
            'Mahavir Marg',
            'Kharghar',
            'Nerul',
            'Noida',
            'Pitampura',
            'Rama Mandi',
            'Saket',
            'Sheikh Sarai',
            'Tanda Road'
        ];

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
                Rule::in($valid_branch_names)
            ],
            "external_internal" => "required",

            // Student Details
            "school_name" => "required|string",
            "student_school_location" => "required|string",
            "student_first_name" => "required",
            "activity" => "required",
            "school_enrollment_no" => "required|string|min:4|regex:/[A-Z]+-[0-9]+/",
            "class" => "required|numeric",
            "section" => "required",

            // Parent Details
            "parent_first_name" => "required",
            "mobile_number" => "regex:/[6-9][0-9]{9}/",
            "email_id" => "email",

            // Fee Details
            "activity_fee" => "required",
            "scholarship_discount" => "numeric",
            "after_discount_fee" => "numeric",
            "final_fee_incl_gst" => "required|numeric",
            "amount" => "numeric",

            // Registration/Booking Fee
            "payments.0.mode_of_payment" => [
                "required",
                Rule::in(ShopifyExcelUpload::payment_modes())
            ],
            "payments.0.amount" => "required|numeric",

            // All Payments
            "payments" => "required",
            "payments.*.amount" => "numeric",
            "payments.*.mode_of_payment" => [
                Rule::in(ShopifyExcelUpload::payment_modes())
            ],
            "payments.*.chequedd_no" => "numeric",
            "payments.*.micr_code" => "numeric",
            "payments.*.chequedd_date" => [
                "regex:" . ShopifyExcelUpload::DATE_REGEX
            ],
            "payments.*.drawee_name" => "string",
            "payments.*.drawee_account_number" => "numeric"
        ];

        $validator = Validator::make($data, $rules);
        $errors = $validator->getMessageBag()->toArray();

        if (! empty($errors)) {
            $this->errors['rows'][$this->row_no] = Arr::flatten(array_values($errors));
        }
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
            $this->errors['sheet'][] = "Cash total mismatch, Entered total $amount_collected_cash, Calculated total " . $modeWiseTotal['cash_total'];
        }
        if ($amount_collected_cheque != $modeWiseTotal['cheque_total']) {
            $this->errors['sheet'][] = "Cheque total mismatch, Entered total $amount_collected_cheque, Calculated total " . $modeWiseTotal['cheque_total'];
        }
        if ($amount_collected_online != $modeWiseTotal['online_total']) {
            $this->errors['sheet'][] = "Online total mismatch, Entered total $amount_collected_online, Calculated total " . $modeWiseTotal['online_total'];
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
                $this->errors['rows'][$this->row_no][] = "Payment " . ($payment_index + 1) . " - Amount is required for any payment.";
                continue;
            }

            $mode = strtolower($payment['mode_of_payment']);
            $amount += $payment['amount'];

            $offline_modes = [
                ShopifyExcelUpload::$modesTitle[ShopifyExcelUpload::MODE_CHEQUE],
                ShopifyExcelUpload::$modesTitle[ShopifyExcelUpload::MODE_DD]
            ];

            $online_modes = [
                ShopifyExcelUpload::$modesTitle[ShopifyExcelUpload::MODE_ONLINE],
                ShopifyExcelUpload::$modesTitle[ShopifyExcelUpload::MODE_PAYTM],
                ShopifyExcelUpload::$modesTitle[ShopifyExcelUpload::MODE_NEFT]
            ];

            $cheque_dd_fields = Arr::only($payment, ShopifyExcelUpload::CHEQUE_DD_FIELDS);
            $online_fields = Arr::only($payment, ShopifyExcelUpload::ONLINE_FIELDS);

            if (in_array($mode, array_map('strtolower', $offline_modes))) {
                // Checking for offline mode payments
                if (array_contains_empty_value($cheque_dd_fields)) {
                    // Checking for blank cheque/dd details
                    $this->errors['rows'][$this->row_no][] = "Payment " . ($payment_index + 1) . " - Cheque/DD Details are mandatory for transactions having payment mode as Cheque/DD.";
                } else {
                    if (DB::check_if_already_used($payment['chequedd_no'], $payment['micr_code'], $payment['drawee_account_number'], $payment_index, $data['shopify_activity_id'], $data['date_of_enrollment'], $data['school_enrollment_no'])) {
                        // Check if the combination of cheque no., micr_code and account_no. exists in database
                        $this->errors['rows'][$this->row_no][] = "Payment " . ($payment_index + 1) . " - Cheque/DD Details already used before.";
                    }
                }
            } else if (in_array($mode, array_map('strtolower', $online_modes))) {
                // Checking for online mode payments
                if (array_contains_empty_value($online_fields)) {
                    // Checking for blank online details
                    $this->errors['rows'][$this->row_no][] = "Payment " . ($payment_index + 1) . " - Transaction Reference No. is mandatory in case of Online Payment.";
                }
            } else if ($mode == strtolower(ShopifyExcelUpload::$modesTitle[ShopifyExcelUpload::MODE_CASH])) {
                // Checking for cash mode payments
                if (! array_contains_empty_value($cheque_dd_fields) || ! array_contains_empty_value($online_fields)) {
                    // Cheque/DD/Online should be blank for cash payments
                    $this->errors['rows'][$this->row_no][] = "Payment " . ($payment_index + 1) . " - For Cash payments, Cheque/DD/Online payment details are not applicable.";
                }
            } else if (! empty($mode)) {
                // Checking for invalid paymemt mode
                $this->errors['rows'][$this->row_no][] = "Payment " . ($payment_index + 1) . " - Invalid Payment Mode - $mode";
            }

            // Function for checking wthether the combination of amount and date present for each installment.
            // The cheque date is being treated as the expected date of collection for the payment.
            if ($payment['type'] == ShopifyExcelUpload::TYPE_INSTALLMENT) {
                if (empty($payment['mode_of_payment'])) {
                    if (empty($payment['amount']) || empty($payment['chequedd_date'])) {
                        $this->errors['rows'][$this->row_no][] = "Payment " . ($payment_index + 1) . " - Expected Amount and Expected date of collection required for every installment of this order.";
                    } else {
                        if (Carbon::now()->diffInDays(Carbon::createFromFormat(ShopifyExcelUpload::DATE_FORMAT, $payment['chequedd_date']), false) > 0) {
                            $except_amount_date = [
                                'amount',
                                'chequedd_date'
                            ];
                            if (! array_contains_empty_value(Arr::except($cheque_dd_fields, $except_amount_date)) || ! array_contains_empty_value($online_fields)) {
                                $this->errors['rows'][$this->row_no][] = "Payment " . ($payment_index + 1) . " - Future Installments with no payment mode cannot have Cheque/DD/Online details";
                            }
                        } else {
                            $this->errors['rows'][$this->row_no][] = "Payment " . ($payment_index + 1) . " - Payment date should be in future for future installments";
                        }
                    }
                }
            }
        }

        if ($amount != $final_fee) {
            $this->errors['rows'][$this->row_no][] = "Total Installment Amount and Final Fee Amount does not match";
        }
    }

    private function ValidateFieldValues(array $data)
    {
        if (empty($data['mobile_number']) && empty($data['email_id'])) {
            $this->errors['rows'][$this->row_no][] = "Either Email or Mobile Number is mandatory.";
        }

        if (! ShopifyExcelUpload::getSchoolLocation($data['delivery_institution'], $data['branch'])) {
            $this->errors['rows'][$this->row_no][] = 'No location exists for Delivery Institution and Branch';
        }

        if (strstr($data['school_name'], ShopifyExcelUpload::SCHOOL_TITLE)) {
            if (strtolower($data['external_internal']) != ShopifyExcelUpload::INTERNAL_ORDER || strtolower($data['delivery_institution']) != strtolower(ShopifyExcelUpload::SCHOOL_TITLE)) {
                $this->errors['rows'][$this->row_no][] = "The order type should be internal for schools under Apeejay Education Society and delivery institution should be Apeejay.";
            }
        } else {
            if (strtolower($data['external_internal']) != ShopifyExcelUpload::EXTERNAL_ORDER || strtolower($data['delivery_institution']) == strtolower(ShopifyExcelUpload::SCHOOL_TITLE)) {
                $this->errors['rows'][$this->row_no][] = "The order type should be external for schools outside Apeejay and delivery institution should be other than Apeejay.";
            }
        }
    }

    private function ValidateActivityDetails(array $data)
    {
        $activity_id = $data['shopify_activity_id'];
        $activity_fee = $data['activity_fee'];
        $final_fee = $data['final_fee_incl_gst'];
        $scholarship_amount = $data['scholarship_discount'];

        if (! DB::shopify_product_database_exists($activity_id)) {
            $this->errors['rows'][$this->row_no][] = "Activity ID is either incorrect or not available.";
        } else if (DB::is_activity_duplicate($activity_id)) {
            $this->errors['rows'][$this->row_no][] = "More than one product exists with Activity ID [$activity_id]";
        } else if (! DB::check_activity_fee_value($activity_fee, $activity_id)) {
            $this->errors['rows'][$this->row_no][] = "Activity Fee entered is incorrect.";
        } else if (empty($data['order_id'])) {
            try {
                $variant_id = DB::get_variant_id($activity_id);
                if (! DB::check_inventory_status($variant_id)) {
                    $this->errors['rows'][$this->row_no][] = "Product is out of stock or disabled.";
                }
            } catch (ModelNotFoundException $e) {
                $this->errors['rows'][$this->row_no][] = "Product does not exists.";
            }
        }

        if (empty($scholarship_amount)) {
            if ($activity_fee != $final_fee) {
                $this->errors['rows'][$this->row_no][] = "Final Fee  is not equal to the activity fee.";
            }
        } else {
            if ($final_fee != ($activity_fee - $scholarship_amount)) {
                $this->errors['rows'][$this->row_no][] = "After applying discount and Final Fee amount does not match.";
            }
        }
    }
}