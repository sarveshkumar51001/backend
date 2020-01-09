<?php

namespace Tests;

use App\Library\Shopify\Excel;
use App\Models\ShopifyExcelUpload;
use Illuminate\Support\Facades\Auth;

class TestCaseData{


    public static function Generate_Raw_Excel($rows){

        $headers = array_keys($rows);

        return (new Excel($headers, $rows, [
            'upload_date' => '02/01/2020',
            'uploaded_by' => "5d1214cbafd58641b5532f82",
            'file_id' => 'shopify-253637',
            'job_status' => ShopifyExcelUpload::JOB_STATUS_PENDING,
            'order_id' => 0,
            'customer_id' => 0
        ]));
    }

    const DATA = [
        'sno' => 1,
        'date_of_enrollment' => '03/01/2020',
        'shopify_activity_id' => 'PR1YR- LW18',
        'delivery_institution' => 'Apeejay',
        'branch' => 'Saket',
        'external_internal' => 'Internal',
        'school_name' => 'Apeejay Saket',
        'student_school_location' => 'Saket',
        'student_first_name' => 'Saumya',
        'student_last_name' => 'Sharma',
        'activity' => '1 year Pratham Law(2018)',
        'school_enrollment_no' => 'SKT-0905',
        'class' => '5',
        'section' => 'C',
        'parent_first_name' => 'Shailesh',
        'parent_last_name' => 'Sharma',
        'mobile_number' => 6767899074,
        'email_id' => '',
        'activity_fee' => 63720,
        'scholarship_discount' => 0,
        'after_discount_fee' => 63720,
        'final_fee_incl_gst' => 63720,
        'amount' => 60000,
        'mode_of_payment' => 'Cash',
        'txn_reference_number_only_in_case_of_paytm_or_online' => NULL,
        'chequedd_no' => NULL,
        'micr_code' => NULL,
        'chequedd_date' => NULL,
        'drawee_name' => NULL,
        'drawee_account_number' => NULL,
        'bank_name' => NULL,
        'bank_branch' => NULL,
        'pdc_collectedpdc_to_be_collectedstatus' => NULL,
        'amount_1' => 3720,
        'txn_reference_number_only_in_case_of_paytm_or_online_1' => NULL,
        'pdc_collectedpdc_to_be_collectedstatus_1' => NULL,
        'mode_of_payment_1' => 'Cheque',
        'chequedd_no_1' => 123456789,
        'micr_code_1' => 45678987657,
        'chequedd_date_1' => "17/07/2019",
        'drawee_name_1' => "test",
        'drawee_account_number_1' => 1232424 ,
        'bank_name_1' => "test",
        'bank_branch_1' => "test",
        'amount_2' => NULL,
        'txn_reference_number_only_in_case_of_paytm_or_online_2' => NULL,
        'pdc_collectedpdc_to_be_collectedstatus_2' => NULL,
        'mode_of_payment_2' => NULL,
        'chequedd_no_2' => NULL,
        'micr_code_2' => NULL,
        'chequedd_date_2' => NULL,
        'drawee_name_2' => NULL,
        'drawee_account_number_2' => NULL,
        'bank_name_2' => NULL,
        'bank_branch_2' => NULL,
        'amount_3' => NULL,
        'txn_reference_number_only_in_case_of_paytm_or_online_3' => NULL,
        'mode_of_payment_3' => NULL,
        'pdc_collectedpdc_to_be_collectedstatus_3' => NULL,
        'chequedd_no_3' => NULL,
        'micr_code_3' => NULL,
        'chequedd_date_3' => NULL,
        'drawee_name_3' => NULL,
        'drawee_account_number_3' => NULL,
        'bank_name_3' => NULL,
        'bank_branch_3' => NULL,
        'amount_4' => NULL,
        'txn_reference_number_only_in_case_of_paytm_or_online_4' => NULL,
        'mode_of_payment_4' => NULL,
        'pdc_collectedpdc_to_be_collectedstatus_4' => NULL,
        'chequedd_no_4' => NULL,
        'micr_code_4' => NULL,
        'chequedd_date_4' => NULL,
        'drawee_name_4' => NULL,
        'drawee_account_number_4' => NULL,
        'bank_name_4' => NULL,
        'bank_branch_4' => NULL,
        'amount_5' => NULL,
        'txn_reference_number_only_in_case_of_paytm_or_online_5' => NULL,
        'mode_of_payment_5' => NULL,
        'pdc_collectedpdc_to_be_collectedstatus_5' => NULL,
        'chequedd_no_5' => NULL,
        'micr_code_5' => NULL,
        'chequedd_date_5' => NULL,
        'drawee_name_5' => NULL,
        'drawee_account_number_5' => NULL,
        'bank_name_5' => NULL,
        'bank_branch_5' => NULL,
        'paid' => 63720,
        'pdc_collected' => 0,
        'pdc_to_be_collected' => 0,
    ];

    const REQUIRED_FLAT_FIELDS = ['date_of_enrollment', 'shopify_activity_id', 'delivery_institution', 'branch',
        'external_internal', 'school_name', 'student_school_location', 'student_first_name', 'activity',
        'school_enrollment_no', 'class', 'section', 'final_fee_incl_gst','parent_first_name'];

    const STRING_FLAT_FIELDS = ['shopify_activity_id', 'school_name', 'student_school_location', 'school_enrollment_no'];

    const NUMERIC_FLAT_FIELDS = ['scholarship_discount', 'after_discount_fee', 'final_fee_incl_gst'];

    const AMOUNT_FLAT_FIELDS = ['final_fee_incl_gst','after_discount_fee'];

    const RULE_IN_FIELDS = ['class','section','branch'];

    const NESTED_NUMERIC_FIELDS = ['amount','chequedd_no','micr_code','drawee_account_number'];
}
