<?php

namespace App\Http\Controllers;

use App\Exports\TransactionsExport;
use App\Models\ShopifyExcelUpload;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Maatwebsite\Excel;

class TransactionController extends BaseController
{
    private $data;

    public function index()
    {
        $breadcrumb = ['Transactions' => ''];
        return view('transactions', ['breadcrumb' => $breadcrumb]);
    }

    public function search_transactions_by_location(Request $request)
    {
        $order_data = [];
        $Orders = [];

        [$start_date,$end_date] = GetStartEndDate(request('daterange'));

        if (!empty($request['location'])){
            if($start_date && $end_date){
            $Orders = ShopifyExcelUpload::where('student_school_location', $request['location'])
                ->whereBetween('payments.upload_date', [$start_date, $end_date])->get();
            }else{
                $Orders = ShopifyExcelUpload::where('student_school_location',$request['location'])->get();
            }
        }

        foreach ($Orders as $Order) {

            $User = User::where('_id',$Order->uploaded_by)->first(['name']);
            $data = [
                'Activity Name' => $Order->activity,
                'Activity Fee' => $Order->activity_fee,
                'Location' => $Order->student_school_location,
                'Student Enrollment No' => $Order->school_enrollment_no,
                'Student Name' => $Order->student_first_name . " " . $Order->student_last_name,
                'class' => $Order->class.$Order->section,
                'Shopify Order Name' => isset($Order->shopify_order_name) ? $Order->shopify_order_name : Null,
                'Uploaded By' => !empty($User) ? $User['name'] : Null
            ];

            if (sizeof($Order['payments']) == 1) {
                $order_data[] = array_merge($data,[
                    'Transaction Amount' => head($Order->payments)['amount'],
                    'Transaction Mode' => head($Order->payments)['mode_of_payment'],
                    'Cheque/DD No' => head($Order->payments)['chequedd_no'],
                    'Cheque/DD Date' => head($Order->payments)['chequedd_date'],
                    'Reference No(PayTM/NEFT)' => head($Order->payments)['txn_reference_number_only_in_case_of_paytm_or_online'],
                    'Transaction Upload Date' => Carbon::createFromTimestamp(head($Order->payments)['upload_date'])->toDateString()
                    ]);
            }else{
                foreach ($Order->payments as $payment) {
                    $order_data[]= array_merge($data,[
                        'Transaction Amount'=> $payment['amount'],
                        'Transaction Mode'=> $payment['mode_of_payment'],
                        'Cheque/DD No' => $payment['chequedd_no'],
                        'Cheque/DD Date' => $payment['chequedd_date'],
                        'Reference No(PayTM/NEFT)' => $payment['txn_reference_number_only_in_case_of_paytm_or_online'],
                        'Transaction Upload Date' => Carbon::createFromTimestamp($payment['upload_date'])->toDateString()
                    ]);
                }
            }
        }
        if(empty($order_data)){
            return view('transactions')->with('order_data',$order_data);
        }
        $this->data = $order_data;

      return Excel\Facades\Excel::download(new TransactionsExport($this->data),'transactions.xlsx');
    }
}
