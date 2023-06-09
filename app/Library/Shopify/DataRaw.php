<?php
namespace App\Library\Shopify;

use App\Models\ShopifyExcelUpload;
use Carbon\Carbon;
use Illuminate\Support\Str;

class DataRaw
{

    public $data = [];

    public static $validNoteAttributes = [
        'amount',
        'mode_of_payment',
        'chequedd_no',
        'micr_code',
        'chequedd_date',
        'drawee_name',
        'drawee_account_number',
        'bank_name',
        'bank_branch',
        'txn_reference_number_only_in_case_of_paytm_or_online'
    ];

    private static $headers = [];

    /**
     * DataRaw constructor.
     *
     * @param array $data
     *
     * @throws \Exception
     */
    public function __construct(array $data)
    {
        if (empty($data)) {
            throw new \Exception('Empty data given');
        }

        $this->data = $data;
    }

    /**
     *
     * @return array
     */
    public function GetData()
    {
        return $this->data;
    }

    /**
     *
     * @return int|mixed
     */
    public function ID()
    {
        return $this->data['_id'] ?? 0;
    }

    public function GetEmail()
    {
        return $this->data['email_id'] ?? '';
    }

    public function GetPhone()
    {
        return $this->data['mobile_number'] ?? '';
    }

    public function GetActivityID()
    {
        return $this->data['shopify_activity_id'] ?? '';
    }

    /**
     * This function returns the enrollment date for the job being processed.
     *
     * @return mixed|string
     */
    public function GetEnrollmentDate()
    {
        return $this->data['date_of_enrollment'] ?? '';
    }

    public function GetOrderID()
    {
        return $this->data['order_id'] ?? 0;
    }

    public function GetJobStatus()
    {
        return $this->data['job_status'] ?? '';
    }

    public function GetActivityFee()
    {
        return $this->data['activity_fee'] ?? 0;
    }

    public function HasInstallment()
    {
        return ($this->data['order_type'] == ShopifyExcelUpload::TYPE_INSTALLMENT);
    }

    public function IsOnlinePayment()
    {
        return (strtolower($this->data['payments'][0]['mode_of_payment']) == 'online');
    }

    /**
     *
     * @return array
     */
    public function GetCustomerCreateData()
    {
        $customerData = [
            "first_name" => $this->data["parent_first_name"],
            "last_name" => $this->data["parent_last_name"],
            "email" => $this->data["email_id"],
            "phone" => (string) $this->data["mobile_number"],
            "metafields" => [
                [
                    "key" => "School Name",
                    "value" => $this->data["school_name"],
                    "value_type" => "string",
                    "namespace" => "student-info"
                ],
                [
                    "key" => "Class",
                    "value" => "Class: " . $this->data["class"],
                    "value_type" => "string",
                    "namespace" => "student-info"
                ],
                [
                    "key" => "Section",
                    "value" => "Section: " . $this->data["section"],
                    "value_type" => "string",
                    "namespace" => "student-info"
                ],
                [
                    "key" => "School Enrollment No.",
                    "value" => $this->data["school_enrollment_no"],
                    "value_type" => "string",
                    "namespace" => "student-info"
                ],
                [
                    "key" => "Parent First Name",
                    "value" => $this->data["parent_first_name"],
                    "value_type" => "string",
                    "namespace" => "student-info"
                ],
                [
                    "key" => "Parent Last Name",
                    "value" => $this->data["parent_last_name"],
                    "value_type" => "string",
                    "namespace" => "student-info"
                ]
            ],
            "tags" => "backend-app"
        ];

        return $customerData;
    }

    /**
     *
     * @param int $productVariantID
     * @param int $customer_id
     *
     * @return array
     * @throws \Exception
     */
    public function GetOrderCreateData($productVariantID, $customer_id)
    {
        if (empty($productVariantID)) {
            throw new \Exception('Empty product variant id given');
        }

        $order_data = [];

        if (! empty($this->data['scholarship_discount'])) {
            $order_data['total_discounts'] = $this->data['scholarship_discount'];
        }

        $order_data['line_items'] = [
            [
                "variant_id" => $productVariantID
            ]
        ];

        $order_data['customer'] = [
            "id" => $customer_id
        ];

        $order_data['processed_at'] = get_iso_date_format($this->GetEnrollmentDate());

        $location = ShopifyExcelUpload::getLocation($this->data['delivery_institution'], $this->data['branch']);

        $order_data['billing_address'] = [
            "first_name" => $this->data['parent_first_name'],
            "last_name" => $this->data['parent_last_name'],
            "address1" => sprintf("%s - %s", $this->data['school_name'], $this->data['student_school_location']),
            "phone" => $this->data['mobile_number'],
            "city" => $location['city'],
            "province" => $location['state'],
            "country" => "India",
            "zip" => $location['pincode']
        ];

        $order_data['shipping_address'] = [
            "first_name" => $this->data['student_first_name'] . " " . $this->data['student_last_name'],
            "last_name" => sprintf("(%s)", $this->data['school_enrollment_no']),
            "address1" => sprintf("%s - %s", $this->data['delivery_institution'], $this->data['branch']),
            "phone" => $this->data['mobile_number'],
            "city" => $location['city'],
            "province" => $location['state'],
            "country" => "India",
            "zip" => $location['pincode']
        ];

        $user_id = $this->data['uploaded_by'];
        $user_email = DB::get_user_email_id_from_database($user_id);

        $tags_array = [];
        $tags_array[] = 'Class: ' . $this->data['class'];
        $tags_array[] = 'Section: ' . $this->data['section'];
        $tags_array[] = $this->data['school_name'];
        $tags_array[] = $this->data['branch'];
        $tags_array[] = $user_email;
        $tags_array[] = 'backend-app';
        $tags_array[] = $this->data['external_internal'];

        if (strtolower($this->data['order_type']) == 'installment') {
            $tags_array[] = 'installments';
        }

        $tags = implode(',', $tags_array);
        $order_data['tags'] = $tags;

        $order_data['transactions'] = [
            [
                "amount" => $this->data['final_fee_incl_gst'],
                "kind" => "authorization"
            ]
        ];

        $order_data["financial_status"] = "pending";

        return $order_data;
    }

    /**
     * Returns Shopify customer fields which need to be updated.
     *
     * @param array $shopifyCustomer
     * @return array
     */
    public function GetCustomerUpdateData($shopifyCustomer)
    {
        $customer_data = [];

        $update_customer_mappings = array(
            "first_name" => "parent_first_name",
            "last_name" => "parent_last_name"
        );

        foreach ($update_customer_mappings as $shopify_key => $excel_key) {
            if ($shopifyCustomer[$shopify_key] != $this->data[$excel_key]) {
                $customer_data += [
                    $shopify_key => (string) $this->data[$excel_key]
                ];
            }
        }

        if($shopifyCustomer['phone'] != "+91". $this->data['mobile_number']) {
            $customer_data += [
                'phone' => (string) $this->data['mobile_number']
            ];
        }

        $tags_array = [];
        $tags_array = explode(',', $shopifyCustomer['tags']);
        if (! in_array('backend-app', $tags_array)) {
            $tags_array[] = 'backend-app';
            $customer_data += [
                'tags' => $tags_array
            ];
        }

        return $customer_data;
    }

    public function GetPaymentData()
    {
        return $this->data['payments'] ?? [];
    }

    public static function GetPaymentDetails(array $payments)
    {
        $notes_array = [];
        $note = "";
        $notes = "";

        foreach ($payments as $installment) {

            if (! empty($installment['mode_of_payment'])) {
                foreach ($installment as $key => $value) {
                    $key = strtolower($key);
                    if (! empty($value) && in_array($key, self::$validNoteAttributes)) {
                        $note = Excel::$headerMap[$key] . ": $value | ";
                        $notes .= $note;
                    }
                }
                $notes_array[] = $notes;
                $notes = "";
            }
        }
        return $notes_array;
    }

    /**
     * This function returns the transaction data to be posted on shopify.
     *
     * Takes a payment array as input and return empty if payment is empty or mode of payment is empty or payment is
     * already processed, if not then create an array transaction data having details like transaction type , amount
     * and the date at which the transaction is being processed and thus finally return it.
     *
     * @param array $payment
     * @param $process_date
     * @return array
     */
    public static function GetTransactionData(array $payment, $process_date)
    {

        if (empty($payment) || empty($payment['mode_of_payment']) || strtolower($payment['processed']) == 'yes') {
            return [];
        }

        $transaction_data = [
            "kind" => "capture",
            "amount" => $payment['amount'],
            "processed_at" => $process_date
        ];

        return $transaction_data;
    }

    public function GetNotes(array $notes_array, $collected_amount)
    {
        $notes_array_packet = [];
        $i = 1;

        foreach ($notes_array as $note) {
            if (empty($note)) {
                continue;
            }

            $notes_packet = [
                "name" => "Payment " . $i,
                "value" => rtrim($note, '| ')
            ];
            $i ++;
            $notes_array_packet[] = $notes_packet;
        }

        $notes_array_packet[] = [
            "name" => "Date of Enrollment",
            "value" => $this->data['date_of_enrollment']
        ];

        $notes_array_packet[] = [
            "name" => "Amount Collected",
            "value" => $collected_amount
        ];

        $notes_array_packet[] = [
            "name" => "Amount Pending",
            "value" => $this->data['final_fee_incl_gst'] - $collected_amount
        ];

        $order_details = [
            "note_attributes" => $notes_array_packet
        ];

        return $order_details;
    }

    /**
     * This function returns the payment process date for one time and installment orders.
     *
     * Takes the payment as input, Initialize the payment process date as the enrollment date for the order and format
     * it as ISO date format using the 'get_iso_date_format' helper function.
     *
     * If the payment mode is cheque/dd then the payment process date is changed to the cheque/dd date else if the order
     * is of type installment and the mode is not cheque/dd then today's date is returned as the payment processed date.
     * @param $installment
     * @return string
     * @throws \Exception
     */
    public function GetPaymentProcessDate($installment) {

        // Initializing payment process date to enrollment date
        $payment_process_date = get_iso_date_format($this->GetEnrollmentDate());

        // If payment type is cheque/DD.
        if(! empty($installment['chequedd_date'])) {
            $payment_process_date = get_iso_date_format($installment['chequedd_date']);
        } elseif($this->HasInstallment()) {
            // If payment type is installment and payment method if not cheque/DD.
            // then make today's date as payment process date
            $payment_process_date = Carbon::now()->toIso8601String();
        }

        return $payment_process_date;
    }

    /* Create slugged headers with count for excel headers
    *
    * @param $header
    * @return string
    */
    public static function getHeaderName($header) {
        $slugged_header = Str::slug($header,'_');
        $updated_header = $slugged_header;
        $index = 1;
        while(true) {
            if(in_array($updated_header, self::$headers)) {
                $updated_header = $slugged_header . "_$index";
                $index++;
            } else {
                self::$headers[] = $updated_header;
                return $updated_header;
            }
        }
    }
}
