<?php

namespace App\Http\Controllers;

use App\Library\Shopify\Reconciliation\Payment;
use App\Models\ShopifyExcelUpload;
use Illuminate\Support\Facades\Auth;
use \Carbon\Carbon;

class HomeController extends BaseController
{
    /**
     * Show the application dashboard.
     */
    public function index()
    {
        return view('home', ['breadcrumb' => ['Dashboard' => '']]);
    }
}
