<?php

namespace App\Library\Shopify;

use App\Http\Controllers\BulkUpload\ShopifyController;
use App\Models\ShopifyExcelUpload;
use App\Models\Student;
use Illuminate\Support\Facades\Auth;

class Search {

    public static function Orders($query,$school_name,$upload_date,$chequedd_date,$mode,$limit)
    {
        $Orders = null;

        if($query){
            $Orders = ShopifyExcelUpload::where('student_first_name', 'like', "%$query%")
                ->orWhere('date_of_enrollment','like',"%$query%")
                ->orWhere('parent_first_name', 'like', "%$query%")
                ->orWhere('school_enrollment_no', 'like', "%$query%")
                ->orWhere('payments.drawee_account_number', 'like', "%$query%")
                ->orWhere('payments.chequedd_no','like',"%$query%")
                ->orWhere('payments.chequedd_date','like',"%$query%")
                ->orWhere('payments.micr_code','like',"%$query%")
                ->orWhere('shopify_order_name', 'like', "%$query%")
                ->orWhere('shopify_activity_id','like',"%$query%")
                ->orWhere('payments.txn_reference_number_only_in_case_of_paytm_or_online', 'like', "%$query%")
                ->orWhere('payments.drawee_name','like',"%$query%")
                ->orWhere('payments.bank_name','like',"%$query%")
                ->orderBy('_id','desc');
        } else{
            $Orders = ShopifyExcelUpload::orderBy('_id','desc');
        }

        if ($school_name) {
            $Orders->where('school_name', $school_name);
        }
        if ($upload_date) {
            [$start_date,$end_date] = GetStartEndDate($upload_date);
            $Orders->whereBetween('payments.upload_date', [$start_date, $end_date]);
        }

        if ($chequedd_date) {

        }
        if ($mode) {
            $Orders->where('payments.mode_of_payment', $mode);
        }

        if(!is_admin()) {
            $Orders->where('uploaded_by', Auth::user()->id);
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
