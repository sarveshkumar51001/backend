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
            'location' => 'required',
            'daterange' => 'required',
            'reco_status' => 'required',
            'activity_list' => 'required'
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

        if(!empty($request['activity_list']) && !in_array('All',$request['activity_list'])) {
            $OrderORM->whereIn('shopify_activity_id',$request['activity_list']);
        }

        if (!empty($request['location'])) {
            if($request['location'] != 'All') {
                $OrderORM->where('student_school_location', $request['location']);
            }
        } else {
            $OrderORM->where('uploaded_by', Auth::user()->id);
        }

        $isOnlyPending = ($request['reco_status'] == ShopifyExcelUpload::PAYMENT_SETTLEMENT_STATUS_DEFAULT);
        /*if(!empty($request['reco_status']) && !in_array($request['reco_status'], ['all', ShopifyExcelUpload::PAYMENT_SETTLEMENT_STATUS_DEFAULT] )) {
            $OrderORM->where('payments.reconciliation.settlement_status', $request['reco_status']);
        }*/

        $OrderORM->whereBetween('payments.upload_date',[$start_date,$end_date]);
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
                'Class' => $Order->class . "-" . $Order->section,
                'Parent Name' => $Order->parent_first_name . " " . $Order->parent_last_name,
                'Activity Fee' => $Order->activity_fee,
                'Scholarship/Discount' => $Order->scholarship_discount
            ];

            if (sizeof($Order['payments']) == 1) {

                $Payment = new Payment(head($Order->payments) ,0);

                $isPdc = ($Order['payments'][0]['is_pdc_payment'] == true && !empty($Order['payments'][0]['chequedd_date'])
                    && Carbon::createFromFormat('d/m/Y', $Order['payments'][0]['chequedd_date'])->timestamp > $end_date);

                // If include unpaid installment toggle is OFF and payment is PDC then skip the payment
                if($isPdc && $exclude_unpaid) {
                    continue;
                }

                if($isOnlyPending && isset($Order['payments'][0]['reconciliation']['settlement_status'])) {
                    continue;
                }

                $order_data[] = array_merge($data, [
                    'Transaction ID' => "'". (head($Order->payments)['transaction_id'] ?? ''),
                    'Transaction Amount' => head($Order->payments)['amount'],
                    'Transaction Mode' => head($Order->payments)['mode_of_payment'],
                    'Reference No(PayTM/NEFT)' => head($Order->payments)['txn_reference_number_only_in_case_of_paytm_or_online'],
                    'Cheque/DD No' => head($Order->payments)['chequedd_no'],
                    'MICR Code' => head($Order->payments)['micr_code'],
                    'Cheque/DD Date' => head($Order->payments)['chequedd_date'],
                    'Drawee Name' => head($Order->payments)['drawee_name'],
                    'Drawee Account Number' => head($Order->payments)['drawee_account_number'],
                    'Bank Name' => head($Order->payments)['bank_name'],
                    'Transaction Upload Date' => Carbon::createFromTimestamp(head($Order->payments)['upload_date'])->format(ShopifyExcelUpload::DATE_FORMAT),
                    'Payment Type' => "Full Payment",
                    'Shopify Order Name' => isset($Order->shopify_order_name) ? $Order->shopify_order_name : Null,
                    'Uploaded By' => !empty($User) ? $User['name'] : Null,
                    'Payment Status' => 'Paid',
                    'Reconciliation Status' => strtoupper($Payment->getRecoStatus()),
                    'Remarks' => $Payment->getRemarks(),
                ]);
            }else{
                foreach ($Order->payments as $index => $payment) {

                    if ($request['reco_status'] == ShopifyExcelUpload::PAYMENT_SETTLEMENT_STATUS_RETURNED
                        || $request['reco_status'] == ShopifyExcelUpload::PAYMENT_SETTLEMENT_STATUS_SETTLED)
                    {
                        if (!isset($payment['reconciliation']['settlement_status'])) {
                            continue;
                        }
                    }

                    $Payment = new Payment($payment, $index);

                    $isPdc = ($payment['is_pdc_payment'] == true && !empty($payment['chequedd_date'])
                        && Carbon::createFromFormat('d/m/Y', $payment['chequedd_date'])->timestamp > $end_date);

                    // If include unpaid installment toggle is OFF and payment is PDC then skip the payment
                    if($isPdc && $exclude_unpaid) {
                        continue;
                    }

                    if($isOnlyPending && isset($payment['reconciliation']['settlement_status'])) {
                        continue;
                    }

                    $order_data[]= array_merge($data,[
                        'Transaction ID' => "'".(head($Order->payments)['transaction_id'] ?? ''),
                        'Transaction Amount'=> $payment['amount'],
                        'Transaction Mode'=> $payment['mode_of_payment'],
                        'Reference No(PayTM/NEFT)' => $payment['txn_reference_number_only_in_case_of_paytm_or_online'],
                        'Cheque/DD No' => $payment['chequedd_no'],
                        'MICR Code' => $payment['micr_code'],
                        'Cheque/DD Date' => $payment['chequedd_date'],
                        'Drawee Name' => $payment['drawee_name'],
                        'Drawee Account Number' => $payment['drawee_account_number'],
                        'Bank Name' => $payment['bank_name'],
                        'Transaction Upload Date' => Carbon::createFromTimestamp($payment['upload_date'])->format(ShopifyExcelUpload::DATE_FORMAT),
                        'Payment Type' => $payment['installment'] == 1 ? 'Registration/Booking Fee':'Installment'." ".$payment['installment'],
                        'Shopify Order Name' => isset($Order->shopify_order_name) ? $Order->shopify_order_name : Null,
                        'Uploaded By' => !empty($User) ? $User['name'] : Null,
                        'Payment Status' => !empty($payment['is_pdc_payment']) && $payment['is_pdc_payment'] ? 'Unpaid' : 'Paid',
                        'Reconciliation Status' => strtoupper($Payment->getRecoStatus()),
                        'Remarks' => $Payment->getRemarks(),
                    ]);
                }
            }
        }

        if(empty($order_data)){
            $request->session()->flash('message', 'No data found for the selected filters!');
            return view('transactions')->with('products',$this->GetUniqueProducts());
        }
        return Excel\Facades\Excel::download(new TransactionsExport($order_data),'transactions.csv');
    }
}
