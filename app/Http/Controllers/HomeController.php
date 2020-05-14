<?php

namespace App\Http\Controllers;

use App\Library\Permission;
use App\Library\Shopify\DB;
use App\Library\Shopify\Reconciliation\Payment;
use App\Models\ShopifyExcelUpload;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;

class HomeController extends BaseController
{
    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index()
    {
        $Orders = \DB::table('shopify_excel_uploads')->where('uploaded_by', Auth::id())->get();

        $reco_data = [
            'all' => [
                'amount' => 0,
                'count' => 0
            ],
            'pending' => [
                'amount' => 0,
                'count' => 0
            ],
            'settled' => [
                'amount' => 0,
                'count' => 0
            ],
            'returned' => [
                'amount' => 0,
                'count' => 0
            ],
        ];

        foreach ($Orders as $Order) {
            foreach ($Order['payments'] as $payment) {
                $Payment = new Payment($payment);

                $reco_data['all']['amount'] += $Payment->getAmount();
                $reco_data['all']['count'] += 1;

                if($Payment->isSettled()) {
                    $reco_data['settled']['amount'] += $Payment->getAmount();
                    $reco_data['settled']['count'] += 1;
                } elseif($Payment->isReturned()) {
                    $reco_data['returned']['amount'] += $Payment->getAmount();
                    $reco_data['returned']['count'] += 1;
                } else {
                    $reco_data['pending']['amount'] += $Payment->getAmount();
                    $reco_data['pending']['count'] += 1;
                }
            }
        }

        $installment_data = $this->installment_analytics_data();


        return view('home')->with('reco_data', $reco_data)->with('installment_data',$installment_data);
    }

    public function installment_analytics_data()
    {
        $installments = [];
        [$accessible_users,$teams] = Permission::has_access_to_users_teams();

        if(!empty($accessible_users)) {
            $Post_Dated_Payments = DB::post_dated_payments()->whereIn('uploaded_by', $accessible_users)->get();
        }else {
            $Post_Dated_Payments = DB::post_dated_payments()->where('uploaded_by', Auth::id())->get();
        }

        foreach($Post_Dated_Payments as $Payment){
            $payment_array = $Payment['payments'];
            $post_payment_keys = array_keys(array_column($payment_array, 'is_pdc_payment'), true);

            foreach($post_payment_keys as $key){
                $timestamp = Carbon::createFromFormat(ShopifyExcelUpload::DATE_FORMAT, $payment_array[$key]['chequedd_date'])->timestamp;

                $data['date'] = $timestamp;
                $data['amount'] = $payment_array[$key]['amount'];

                $installments[] = $data;
            }
        }

        $installment_data = [
            'all' => [
                'amount' => 0,
                'count' => 0
            ],
            'expired' => [
                'amount' => 0,
                'count' => 0
            ],
            'seven_days' => [
                'amount' => 0,
                'count' => 0
            ],
            'eight_to_thirty_days' => [
                'amount' => 0,
                'count' => 0
            ],
        ];

        foreach($installments as $installment){

            $eight_days_timestamp = Carbon::today()->addDays(8)->timestamp;
            $thirty_days_timestamp = Carbon::today()->addDays(30)->timestamp;

            $installment_data['all']['amount'] += $installment['amount'];
            $installment_data['all']['count'] += 1;

            if($installment['date'] < time()){
                $installment_data['expired']['amount'] += $installment['amount'];
                $installment_data['expired']['count'] += 1;
            } elseif (Carbon::today()->diffInDays(Carbon::createFromTimestamp($installment['date']), false) <= 7){
                $installment_data['seven_days']['amount'] += $installment['amount'];
                $installment_data['seven_days']['count'] += 1;
            } elseif ($installment['date'] >= $eight_days_timestamp && $installment['date'] <= $thirty_days_timestamp){
                $installment_data['eight_to_thirty_days']['amount'] += $installment['amount'];
                $installment_data['eight_to_thirty_days']['count'] += 1;
            }
        }

        return $installment_data;
    }
}
