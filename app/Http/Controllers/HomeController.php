<?php

namespace App\Http\Controllers;

use App\Library\Shopify\Reconciliation\Payment;
use App\Models\ShopifyExcelUpload;
use Illuminate\Support\Facades\Auth;

class HomeController extends BaseController
{
    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {

//    	$shopify = \Shopify::setShopUrl('valedra.myshopify.com')->setAccessToken(env('SHOPIFY_ACCESS_TOKEN'));
//
//    	// @see https://help.shopify.com/en/api/getting-started/search-syntax
////	    dd($shopify->get("admin/customers/search.json", ["query" => "phone:9899477299", "limit"=>20]));
////	    dd($shopify->get("admin/customers/search.json", ["query" => "email:bishwanathkj@gmail.com", "limit"=>20]));
//	    dd($shopify->get("admin/customers/search.json", ["query" => "email:bishwanathkj@gmail.com OR phone:9899477299", "limit"=>20]));

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


        return view('home')->with('reco_data', $reco_data);
    }
}
