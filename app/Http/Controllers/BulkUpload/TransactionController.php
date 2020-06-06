<?php

namespace App\Http\Controllers\BulkUpload;

use App\Exports\TransactionsExport;
use App\Http\Controllers\BaseController;
use App\Library\Permission;
use App\Library\Shopify\Reconciliation\Payment;
use App\Models\ShopifyExcelUpload;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel;

class TransactionController extends BaseController
{

    public function index()
    {
        if(!has_permission(Permission::TRANSACTIONS_VIEW)){
            return view('errors.403');
        }

        $breadcrumb = ['Transactions' => ''];

        return view('transactions', ['breadcrumb' => $breadcrumb,'products'=> $this->GetUniqueProducts()]);
    }

    public function GetUniqueProducts(){
        return ShopifyExcelUpload::groupBy('shopify_activity_id')->pluck('shopify_activity_id')->toArray();
    }

    public function search_transactions_by_location(Request $request)
    {
        if(!has_permission(Permission::TRANSACTIONS_VIEW)){
            return view('errors.403');
        }
        $order_data = [];

        $rules = [
            'daterange' => 'required',
            'reco_status' => 'required'
        ];

        Validator::make($request->all(), $rules)->validate();

        // As per the form, unpaid installments are initially excluded from the output data
        // hence exclude_unpaid is set to be true, if the unpaid installments included toggle is
        // ON exclude_unpaid is false and the unpaid installments will be included in the data.
        $exclude_unpaid = true;
        if($request['unpaid_active'] == 'on'){
            $exclude_unpaid = false;
        }

        [$start_date,$end_date] = GetStartEndDate(request('daterange'));

        $OrderORM = ShopifyExcelUpload::orderBy('_id');

        if(isset($request['activity_list']) && !empty($request['activity_list'])){
            $OrderORM->whereIn('shopify_activity_id',$request['activity_list']);
        }

        if (isset($request['location']) && !empty($request['location'])) {
            if($request['location'] == 'All'){
                $OrderORM->whereBetween('payments.upload_date',[$start_date,$end_date]);
            } else{
            $OrderORM->where('student_school_location', $request['location'])
                ->whereBetween('payments.upload_date', [$start_date, $end_date]);
            }
        } else {
            $OrderORM->where('uploaded_by', Auth::user()->id)
                ->whereBetween('payments.upload_date', [$start_date, $end_date]);
        }

        if(isset($request['reco_status']) && !empty($request['reco_status']) && !in_array($request['reco_status'], ['all', ShopifyExcelUpload::PAYMENT_SETTLEMENT_STATUS_DEFAULT] )) {
            $OrderORM->where('payments.reconcilation.settlement_status', $request['reco_status']);
        }

        $Orders = $OrderORM->get();

        foreach ($Orders as $Order) {

            $User = User::where('_id',$Order->uploaded_by)->first(['name']);
            $data = [
                'Date of Enrollment' => $Order->date_of_enrollment,
                'Shopify Activity ID' => $Order->shopify_activity_id,
                'Delivery Institution' => $Order->delivery_institution,
                'Location' => $Order->student_school_location,
                'School Name' => $Order->school_name,
                'Student Name' => $Order->student_first_name . " " . $Order->student_last_name,
                'Activity Name' => $Order->activity,
                'Student Enrollment No' => $Order->school_enrollment_no,
                'Class' => $Order->class.$Order->section,
                'Parent Name' => $Order->parent_first_name . " " . $Order->parent_last_name,
                'Activity Fee' => $Order->activity_fee,
                'Scholarship/Discount' => $Order->scholarship_discount
            ];

            if (sizeof($Order['payments']) == 1) {

                $Payment = new Payment(head($Order->payments) ,0);

                    $order_data[] = array_merge($data, [
                        'Transaction Amount' => head($Order->payments)['amount'],
                        'Transaction Mode' => head($Order->payments)['mode_of_payment'],
                        'Reference No(PayTM/NEFT)' => head($Order->payments)['txn_reference_number_only_in_case_of_paytm_or_online'],
                        'Cheque/DD No' => head($Order->payments)['chequedd_no'],
                        'MICR Code' => head($Order->payments)['micr_code'],
                        'Cheque/DD Date' => head($Order->payments)['chequedd_date'],
                        'Drawee Name' => head($Order->payments)['drawee_name'],
                        'Drawee Account Number' => head($Order->payments)['drawee_account_number'],
                        'Bank Name' => head($Order->payments)['bank_name'],
                        'Transaction Upload Date' => Carbon::createFromTimestamp(head($Order->payments)['upload_date'])->toDateString(),
                        'Payment Type' => "Full Payment",
                        'Shopify Order Name' => isset($Order->shopify_order_name) ? $Order->shopify_order_name : Null,
                        'Parent Order Name' => Null,
                        'Uploaded By' => !empty($User) ? $User['name'] : Null,
                        'Reconciliation Status' => strtoupper($Payment->getRecoStatus()),
                        'Payment Status' => $Payment->isProcessed() ? 'Paid':'Unpaid'
                    ]);
            }else{
                foreach ($Order->payments as $index => $payment) {

                    $Payment = new Payment($payment, $index);

                    $order_data[]= array_merge($data,[
                        'Transaction Amount'=> $payment['amount'],
                        'Transaction Mode'=> $payment['mode_of_payment'],
                        'Reference No(PayTM/NEFT)' => $payment['txn_reference_number_only_in_case_of_paytm_or_online'],
                        'Cheque/DD No' => $payment['chequedd_no'],
                        'MICR Code' => $payment['micr_code'],
                        'Cheque/DD Date' => $payment['chequedd_date'],
                        'Drawee Name' => $payment['drawee_name'],
                        'Drawee Account Number' => $payment['drawee_account_number'],
                        'Bank Name' => $payment['bank_name'],
                        'Transaction Upload Date' => Carbon::createFromTimestamp($payment['upload_date'])->toDateString(),
                        'Payment Type' => $payment['installment'] == 1 ? 'Registration/Booking Fee':'Installment'." ".$payment['installment'],
                        'Shopify Order Name' => isset($Order->shopify_order_name) ? $Order->shopify_order_name : Null,
                        'Parent Order Name' => isset($Order->shopify_order_name) ? $Order->shopify_order_name : Null,
                        'Uploaded By' => !empty($User) ? $User['name'] : Null,
                        'Reconciliation Status' => strtoupper($Payment->getRecoStatus()),
                        'Payment Status' => $Payment->isProcessed() ? 'Paid':'Unpaid'
                    ]);
                }
            }
        }
        // If include unpaid installment toggle is OFF, filter the array and show only the payments
        // where payment status is Paid.
        if($exclude_unpaid){
            $order_data = Arr::where($order_data,function ($payment, $key) {
                return $payment['Payment Status'] == 'Paid';
            });
        }

        if(empty($order_data)){
            return view('transactions')->with('order_data',$order_data)
                ->with('products',$this->GetUniqueProducts());
        }

      return Excel\Facades\Excel::download(new TransactionsExport($order_data),'transactions.xlsx');
    }
}
