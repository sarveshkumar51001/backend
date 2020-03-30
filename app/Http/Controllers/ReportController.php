<?php


namespace App\Http\Controllers;

use App\Exports\ReportExport;
use App\Library\Shopify\Reconciliation\Validate;
use App\Library\Shopify\Report;
use App\Models\ShopifyExcelUpload;
use App\Models\Student;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel;

Class ReportController extends BaseController
{

    public function main()
    {
        $data = [];
        $report_type = '';
        $breadcrumb = ['Reports' => ''];

        if(\Request::isMethod('post')) {

            $report_type = !empty(request('report-type')) ? request('report-type') : '';

            $date_params = getStartEndDate(request('daterange'));
            [$start_date, $end_date] = $date_params;
            $location = !empty(request('school-name')) ? explode(' ', request('school-name')) : [];

            if(!empty($location) && Report::ValidateLocation($location)) {
                if ($report_type == '1') {
                    $data = Report::getBankChequeDepositData($start_date, $end_date, $location);
                }
            }
            //
            // For more reports....
            //

            $filename = !empty($report_type) ? sprintf("%s.csv", Report::REPORT_MAPPING[request('report-type')]['name']) : '';

            if (!empty(request('download-csv'))) {
                return Excel\Facades\Excel::download(new ReportExport($data), $filename);
            }
        }
        session()->flashInput(request()->input());
        return view('shopify.reports-main',['breadcrumb' => $breadcrumb,'data' =>$data,'param' => request()->method(),'type' => $report_type]);
    }
}
