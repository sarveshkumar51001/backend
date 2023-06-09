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
            if (empty(request('report-type')) || request('report-type') != '1') {
	            return response('Please select valid report type', 404);
            }

            $report_type = !empty(request('report-type')) ? request('report-type') : '';

            $date_params = getStartEndDate(request('daterange'));
            [$start_date, $end_date] = $date_params;

            // Break the school name into delivery_institution and branch
	        if (request('school-name') == '-1' && is_admin()) {
	            $delivery_institution = '-1';
	            $branch = '-1';
            } else {
	            $location = explode(' ', request('school-name'), 2);
	            $delivery_institution = $location[0] ?? "";
	            $branch = $location[1] ?? "";
            }

	        // Make sure required data is there and also it show be a valid location if not given all org
	        if(empty($delivery_institution) || empty($branch) || ($delivery_institution != '-1' && !Report::ValidateLocation($delivery_institution, $branch))) {
		        return response('Invalid data given, please check input options.', 404);
	        }

	        // Make sure if User has selected specific org and he is not admin then we need to check for his access as well
	        if($delivery_institution != '-1' && !is_admin() && !Report::ValidateLocationAccess($delivery_institution, $branch)) {
		        return response('Invalid data given, please check input options.', 404);
	        }

	        $data = Report::getBankChequeDepositData($start_date, $end_date, $delivery_institution, $branch);

	        $filename = !empty($report_type) ? sprintf("%s.xls", Report::REPORT_MAPPING[request('report-type')]['name']) : '';

            if (!empty(request('download-csv')) && !empty($data)) {
                return Excel\Facades\Excel::download(new ReportExport($data), $filename);
            }
        }
        session()->flashInput(request()->input());
        return view('shopify.reports-main', ['breadcrumb' => $breadcrumb, 'data' => $data, 'param' => request()->method(), 'type' => $report_type]);
    }
}
