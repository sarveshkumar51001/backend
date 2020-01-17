<?php

namespace App\Library\Shopify;

use App\Models\ShopifyExcelUpload;
use App\Models\Student;

class Search {

    public static function Orders($query,$school_name,$date,$mode,$limit)
    {
        $Orders = ShopifyExcelUpload::where('student_first_name', 'like', "%$query%")
            ->orWhere('parent_first_name', 'like', "%$query%")
            ->orWhere('school_enrollment_no', 'like', "%$query%")
            ->orWhere('payments.drawee_account_number', 'like', "%$query%")
            ->orWhere('shopify_order_name', 'like', "%$query%")
            ->orWhere('payments.txn_reference_number_only_in_case_of_paytm_or_online', 'like', "%$query%");

        if (!empty($school_name)) {
            $Orders->where('school_name', $school_name);
        }
        if (!empty($date)) {
            $Orders->orWhere('date_of_enrollment', $date)
                ->orWhere('upload_date', $date)
                ->orWhere('payments.chequedd_date', $date);
        }
        if (!empty($mode)) {
            $Orders->where('payments.mode_of_payment', $mode);
        }

        return $Orders->paginate($limit);
    }

    public static function Students($query,$school_name,$limit)
    {
        $Students = Student::where('student_first_name', 'like', "%$query%")
            ->orWhere('parent_first_name', 'like', "%$query%")
            ->orWhere('school_enrollment_no', 'like', "%$query%");

        if(!empty($school_name)){
            $Students->where('school_name',$school_name);
        }

        return $Students->paginate($limit);
    }
}
