<?php

namespace App\Http\Controllers;

use App\Exports\InstaLeadsExport;
use App\Library\Instapage\WebhookDataInstapage;
use Illuminate\Support\Facades\Validator;
use App\Models\InstaPage;
use Maatwebsite\Excel;

class InstaLeadController extends BaseController
{

    public function leads()
    {
        $Pages = InstaPage::all();
        $data = [];
        $breadcrumb = ['List' => ''];

        if (\Request::isMethod('post')) {

            $rules = [
                "page_id" => "required",
                "daterange" => "required|string"
            ];

            $validator = Validator::make(request()->all(), $rules);
            if ($validator->fails()) {
                return redirect()->route('revenue.reports')->withErrors($validator, 'Errors')->withInput();
            }

            $page_id = !empty(request('page_id')) ? request('page_id') : '';
            $date_params = getStartEndDate(request('daterange'));
            [$start_date, $end_date] = $date_params;

            $LeadsData = WebhookDataInstapage::getInstaPageList($start_date, $end_date,  $page_id);
            foreach ($LeadsData as $value) {
                $data[] = [
                    'Full Name'=> $value['data']['body']['Full Name'],
                    'Email'=> $value['data']['body']['Email'],
                    'Mobile' => $value['data']['body']['Mobile'],
                    'School' => $value['data']['body']['School']
                ];
            }

            $InstaPage = InstaPage::where(InstaPage::PageId,$page_id)->first(['page_name']);
            $filename = !empty($page_id) ? sprintf("%s.xls", $InstaPage['page_name']) : '';

            if (!empty(request('download-csv')) && !empty($data)) {
                return Excel\Facades\Excel::download(new InstaLeadsExport($data), $filename);
            }

        }
        session()->flashInput(request()->input());
        return view('instagram-leads.leads',['Pages'=>$Pages, 'data' => $data, 'breadcrumb' => $breadcrumb,'param' => request()->method()]);
    }
}
