<?php

namespace Tests;

class TestCaseData{

    const DATA = [
    'sno' => 1,
    'date_of_enrollment' => '28/11/2019',
    'shopify_activity_id' => 'PR1YR- LW18',
    'delivery_institution' => 'Apeejay',
    'branch' => 'ASM Dwarka',
    'external_internal' => 'Internal',
    'school_name' => 'Apeejay Saket',
    'student_school_location' => 'Saket',
    'student_first_name' => 'Saumya',
    'student_last_name' => 'Sharma',
    'activity' => '1 year Pratham Law(2018)',
    'school_enrollment_no' => 'SKT-1918',
    'class' => '2',
    'section' => 'C',
    'parent_first_name' => 'Suresh',
    'parent_last_name' => 'Sharma',
    'mobile_number' => 8128384854,
    'email_id' => 'rahulsharma@fake.com',
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
    'chequedd_no_1' => NULL,
    'micr_code_1' => NULL,
    'chequedd_date_1' => 28/11/2019,
    'drawee_name_1' => NULL,
    'drawee_account_number_1' => NULL,
    'bank_name_1' => NULL,
    'bank_branch_1' => NULL,
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

    const EXPECTED_ERRORS_FLAT_FIELDS = [

        "date_of_enrollment" => ["required","regex"],
        "shopify_activity_id" => ["required","string"],
        "delivery_institution" => ["required"],
        "branch" => ["required","in"],
        "external_internal" => ["required"],
        "school_name" => ["required","string"],
        "student_school_location" => ["required","string"],
        "student_first_name" => ["required"],
        "activity" => ["required"],
        "school_enrollment_no" => ["required","string"],
        "class" => ["required","in"],
        "section" => ["required","in"],
        "parent_first_name" => ["required"],
        "mobile_number" => ["regex","not_exponential"],
        "email_id" => ["email"],
        "activity_fee" => ["required"],
        "scholarship_discount" => ["numeric"],
        "after_discount_fee" => ["numeric","amount"],
        "final_fee_incl_gst" => ["required","numeric","amount"],
        "amount" => ["numeric","amount"],
        "payments.0.mode_of_payment" => ["required","in"],
        "payments.0.amount" => ["required","numeric","amount"],
        "payments" => ["required"]
    ];

}
