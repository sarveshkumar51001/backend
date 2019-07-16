<?php
namespace App\Library\Shopify;

use App\Models\ShopifyExcelUpload;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

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

        // Finding data validation errors
        foreach ($this->FileFormattedData as $index => $data) {
            $this->row_no ++;
            if ($this->ValidateDuplicateRow($data)) {
                unset($this->FileFormattedData[$index]);
                continue;
            }

            $this->ValidateData($data);
            $this->ValidateInstallmentFinalFee($data);
            $this->ValidateChequeDetails($data);
            $this->ValidateFieldValues($data);
            $this->ValidateDate($data);
            $this->ValidateExpectedAmountDate($data);
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
            $except = [
                'payments',
                'file_id',
                'job_status',
                'order_id',
                'customer_id',
                'upload_date'
            ];
            foreach (Arr::except($row, $except) as $index => $value) {
                if ($value != $DatabaseRow[$index]) {
                    $fields_updated[] = $index;
                }
            }

            if (! empty($fields_updated)) {
                $this->errors['rows'][$this->row_no][] = "Only Payment data can be updated for an Order. Field(s) " . implode($fields_updated, ",") . " has been changed";
            }

            foreach ($row["payments"] as $payment_index => $payment) {
                if ($DatabaseRow[$payment_index]['processed'] == 'Yes') {
                    if ($DatabaseRow[$payment_index]['amount'] != $payment['amount'] || $DatabaseRow[$payment_index]['chequedd_date'] != $payment['chequedd_date'] || $DatabaseRow[$payment_index]['mode_of_payment'] != $payment['mode_of_payment']) {
                        $is_duplicate = false;
                        $this->errors['rows'][$this->row_no][] = "Already Processed installments can't be modified. Installment $payment_index have been modified";
                    }
                }
            }
        }

        if ($is_duplicate) {
            $this->errors['rows'][$this->row_no][] = "This order has already been processed";
        }

        return $is_duplicate;
    }

    private function ValidateInstallmentFinalFee(array $data)
    {
        $total_installment_amount = array_sum(array_column($data['payments'], 'amount'));
        $final_fee = $data['final_fee_incl_gst'];

        if ($total_installment_amount != $final_fee) {
            $this->errors['rows'][$this->row_no][] = "Total Installment Amount ($total_installment_amount) and Final Fee Amount ($final_fee) does not match";
        }
    }

    private function ValidateData(array $data)
    {
        $valid_branch_names = [
            'Faridabad 15',
            'Charkhi Dadri',
            'Faridabad 21 D',
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
            'Sheikh Sarai'
        ];

        $rules = [
            "shopify_activity_id" => "required|string|min:3",
            "school_name" => "required|string",
            "school_enrollment_no" => "required|string|min:4|regex:/[A-Z]+-[0-9]+/",
            "mobile_number" => "regex:/[6-9][0-9]{9}/",
            "email_id" => "email",
            "date_of_enrollment" => "required",
            "activity_fee" => "required",
            "final_fee_incl_gst" => "required|numeric",
            "scholarship_discount" => "numeric",
            "student_first_name" => "required",
            "class" => "required",
            "section" => "required",
            "parent_first_name" => "required",
            "branch" => [
                "required",
                Rule::in($valid_branch_names)
            ],
            "activity" => "required",
            "payments.*.chequedd_no" => "numeric",
            "payments.*.drawee_name" => "string",
            "payments.*.drawee_account_number" => "numeric",
            "payments.*.micr_code" => "numeric",
            "external_internal" => "required",
            "payments.*.amount" => "numeric",
            "payments" => "required",
            "payments.0.mode_of_payment" => "required|string",
            "payments.0.amount" => "required|numeric",
            "payments.*.mode_of_payment" => "string"
        ];

        $validator = Validator::make($data, $rules);
        $errors = $validator->getMessageBag()->toArray();

        if (! empty($errors)) {
            $this->errors['rows'][$this->row_no] = $errors;
        }
    }

    private function ValidateAmount()
    {
        if (count($this->FileFormattedData) == 0)
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
            $this->errors['sheet'][] = "Cheque total mismatch, Entered total $amount_collected_cash, Calculated total " . $modeWiseTotal['cheque_total'];
        }
        if ($amount_collected_online != $modeWiseTotal['online_total']) {
            $this->errors['sheet'][] = "Online total mismatch, Entered total $amount_collected_cash, Calculated total " . $modeWiseTotal['online_total'];
        }
    }

    public function HasAllValidHeaders()
    {
        foreach ($this->File->GetFormattedHeader() as $header) {
            if (! isset(Excel::$headerMap[$header])) {
                return false;
            }
        }

        return true;
    }

    /**
     *
     * @return array
     */
    private function get_amount_total()
    {
        $cashTotal = $chequeTotal = $onlineTotal = 0;
        $PreviousCashTotal = $PreviousChequeTotal = $PreviousOnlineTotal = 0;

        foreach ($this->FileFormattedData as $index => $row) {
            // Get the primary combination to lookup in database

            $date_enroll = $row['date_of_enrollment'];
            $activity_id = $row['shopify_activity_id'];
            $std_enroll_no = $row['school_enrollment_no'];

            $DatabaseRow = ShopifyExcelUpload::where('date_of_enrollment', $date_enroll)->where('shopify_activity_id', $activity_id)
                ->where('school_enrollment_no', $std_enroll_no)
                ->first();

            if (! empty($DatabaseRow)) {
                foreach ($DatabaseRow['payments'] as $payment) {
                    if ($payment['processed'] == 'Yes')
                        continue;
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
                    } else {
                        $this->errors['rows'][$this->row_no][] = "Invalid mode_of_payment [$paymentMode] received for row no " . ($index + 1);
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

    private function ValidateChequeDetails(array $data)
    {
        foreach ($data['payments'] as $payment) {
            $mode = strtolower($payment['mode_of_payment']);
            if ($mode == strtolower(ShopifyExcelUpload::$modesTitle[ShopifyExcelUpload::MODE_CHEQUE]) || $mode == strtolower(ShopifyExcelUpload::$modesTitle[ShopifyExcelUpload::MODE_DD])) {

                $cheque_no = $payment['chequedd_no'];
                $account_no = $payment['drawee_account_number'];
                $micr_code = $payment['micr_code'];

                if (! empty($cheque_no) && ! empty($account_no) && ! empty($micr_code)) {
                    // Check if the combination of cheque no., micr_code and account_no. exists in database
                    if (DB::check_if_already_used($cheque_no, $micr_code, $account_no)) {
                        $this->errors['rows'][$this->row_no][] = "Cheque/DD Details already used before.";
                    }
                }
            }
        }
    }

    private function ValidateFieldValues(array $data)
    {
        foreach ($data['payments'] as $payment) {
            $mode = strtolower($payment['mode_of_payment']);
            $amount = $payment['amount'];

            if ($mode == strtolower(ShopifyExcelUpload::$modesTitle[ShopifyExcelUpload::MODE_ONLINE]) || $mode == strtolower(ShopifyExcelUpload::$modesTitle[ShopifyExcelUpload::MODE_PAYTM]) || $mode == strtolower(ShopifyExcelUpload::$modesTitle[ShopifyExcelUpload::MODE_NEFT])) {
                if (empty($payment['txn_reference_number_only_in_case_of_paytm_or_online'])) {
                    $this->errors['rows'][$this->row_no][] = "Transaction Reference No. is mandatory in case of online and Paytm transactions.";
                }
            }

            if ($mode == strtolower(ShopifyExcelUpload::$modesTitle[ShopifyExcelUpload::MODE_CHEQUE]) || $mode == strtolower(ShopifyExcelUpload::$modesTitle[ShopifyExcelUpload::MODE_DD])) {
                if (empty($payment['chequedd_date']) || empty($payment['chequedd_no']) || empty($payment['micr_code']) || empty($payment['drawee_account_number']) || empty($payment['drawee_name']) || empty($payment['bank_name']) || empty($payment['bank_branch'])) {
                    $this->errors['rows'][$this->row_no][] = " All Cheque Details are mandatory for transactions having payment mode as cheque.";
                }
            }

            if ($amount > $data['final_fee_incl_gst']) {
                $this->errors['rows'][$this->row_no][] = "Amount captured as payment is more than the final value of the order.";
            }
        }

        if (empty($data['mobile_number']) && empty($data['email_id'])) {
            $this->errors['rows'][$this->row_no][] = "Either Email or Mobile Number is mandatory.";
        }

        if (strstr($data['school_name'], ShopifyExcelUpload::SCHOOL_TITLE) && strtolower($data['external_internal']) != ShopifyExcelUpload::INTERNAL_ORDER) {
            $this->errors['rows'][$this->row_no][] = "The order type should be internal for schools under Apeejay Education Society.";
        }

        if (! strstr($data['school_name'], ShopifyExcelUpload::SCHOOL_TITLE) && strtolower($data['external_internal']) != ShopifyExcelUpload::EXTERNAL_ORDER) {
            $this->errors['rows'][$this->row_no][] = "The order type should be external for schools outside Apeejay.";
        }
    }

    private function ValidateDate(array $data)
    {
        if (! preg_match(ShopifyExcelUpload::DATE_REGEX, $data['date_of_enrollment'])) {
            $this->errors['rows'][$this->row_no][] = "Incorrect format of enrollment date. The correct format is {DD/MM/YYYY} i.e. 01/07/2019";
        }

        foreach ($data['payments'] as $index => $payment) {
            $chequedd_date = $payment['chequedd_date'];
            if (! empty($chequedd_date)) {
                if (! preg_match(ShopifyExcelUpload::DATE_REGEX, $chequedd_date)) {
                    $this->errors['rows'][$this->row_no][] = "Incorrect format of date in payment no. " . ($index + 1) . " The correct format is {DD/MM/YYYY} i.e. 01/07/2019";
                }
            }
        }
    }

    // Function for checking wthether the combination of amount and date present for each installment. THe cheque date is being treated as the expected date of collection for the payment.
    Private function ValidateExpectedAmountDate(array $data)
    {
        $total_amount = 0;
        foreach ($data['payments'] as $payment) {
            if ($payment['type'] == ShopifyExcelUpload::TYPE_INSTALLMENT) {
                if (empty($payment['mode_of_payment'])) {
                    if (empty($payment['amount']) || empty($payment['chequedd_date'])) {
                        $this->errors['rows'][$this->row_no][] = "Expected Amount and Expected date of collection required for every installment of this order.";
                    }
                }
            }

            $total_amount += $payment['amount'];
        }

        if ($total_amount != $data['final_fee_incl_gst']) {
            $this->errors['rows'][$this->row_no][] = "Sum of all the payments to be made should not be more or less than the final fee of the order.";
        }
    }

    private function ValidateActivityDetails(array $data)
    {
        $activity_id = $data['shopify_activity_id'];
        $activity_fee = $data['activity_fee'];
        $final_fee = $data['final_fee_incl_gst'];
        $activity_name = $data['activity'];

        
        if (! DB::get_shopify_product_from_database($activity_id,$activity_name)) {
            $this->errors['rows'][$this->row_no][] = "Either activity id or sctivity name is incorrect.";
        }

        if (! DB::check_activity_fee_value($activity_fee, $activity_id)) {
            $this->errors['rows'][$this->row_no][] = "Activity Fee entered is incorrect.";
        }

        if (empty($data['scholarship_discount'])) {
            if ($activity_fee != $final_fee) {
                $this->errors['rows'][$this->row_no][] = "Final Fee  is not equal to the activity fee.";
            }
        }
    }
}