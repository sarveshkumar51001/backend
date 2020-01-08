<?php

namespace App\Http\Controllers;

use Illuminate\Support\Str;

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
        return view('home');
    }
}
