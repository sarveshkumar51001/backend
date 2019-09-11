<?php

namespace App\Http\Controllers;

use App\Models\ShopifyExcelUpload;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

Class ReportController extends BaseController
{
    public function main(){

        return view('shopify.reports-main');
    }

    public function render_reports(Request $request){

        $date_params = ShopifyExcelUpload::getStartEndDate(request('daterange'));
        [$start_date,$end_date] = $date_params;
        $report_data = ShopifyExcelUpload::where('uploaded_by',Auth::id())->whereBetween('payments.upload_date', [$start_date, $end_date])->get()->toArray();

        return view('shopify.reports-main')->with('report_data',$report_data);

    }
}
