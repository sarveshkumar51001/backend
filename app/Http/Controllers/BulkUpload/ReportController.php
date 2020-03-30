<?php


namespace App\Http\Controllers\BulkUpload;

use App\Exports\ReportExport;
use App\Http\Controllers\BaseController;
use App\Library\Shopify\Report;

use App\Models\ShopifyExcelUpload;
use App\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel;

Class ReportController extends BaseController
{

    public function main()
    {
        $data = [];
        $report_type = '';
        $breadcrumb = ['Reports' => ''];

        if (\Request::isMethod('post')) {

            $rules = [
                "report-type" => "required",
                "school-name" => "required",
                "daterange" => "required|string"
            ];

            $validator = Validator::make(request()->all(), $rules);

            if ($validator->fails()) {
                return redirect()->route('revenue.reports')->withErrors($validator, 'Errors')->withInput();
            }

            $report_type = !empty(request('report-type')) ? request('report-type') : '';

            $date_params = getStartEndDate(request('daterange'));
            [$start_date, $end_date] = $date_params;
            $location = !empty(request('school-name')) ? explode(' ', request('school-name'), 2) : [];

            $admin = false;
            if(head($location) == ShopifyExcelUpload::ALL_SCHOOLS){
                $admin = true;
            }

            if (!empty($location)) {
                if (Report::ValidateLocation($location) || is_admin()) {
                    if ($report_type == '1') {
                        $data = Report::getBankChequeDepositData($start_date, $end_date, $location,$admin);
                    }
                } else {
                    return response('You don\'t have access to the organization selected nor you are an admin', 404);
                }
            }
            //
            // For more reports....
            //
            $filename = !empty($report_type) ? sprintf("%s.xls", Report::REPORT_MAPPING[request('report-type')]['name']) : '';

            if (!empty(request('download-csv')) && !empty($data)) {
                return Excel\Facades\Excel::download(new ReportExport($data), $filename);
            }
        }
        session()->flashInput(request()->input());
        return view('shopify.reports-main', ['breadcrumb' => $breadcrumb, 'data' => $data, 'param' => request()->method(), 'type' => $report_type]);
    }
}
