<?php

namespace App\Http\Controllers;

use Maatwebsite\Excel;
use App\Models\InstaPage;
use App\Exports\InstaLeadsExport;
use App\Library\Instapage\WebhookDataInstapage;

/**
 * Class PageController
 * @package App\Http\Controllers
 */
class PageController extends BaseController
{
    public function leads()
    {
        $Pages = InstaPage::all();
        $LeadsData = [];
        $InstaPage = [];
        $breadcrumb = ['List' => ''];

        if (\Request::isMethod('get')) {

            $page_id = !empty(request('page_id')) ? request('page_id') : '';
            $date_params = getStartEndDate(request('daterange'));
            [$start_date, $end_date] = $date_params;

            $LeadsData = WebhookDataInstapage:: getInstaPageList($start_date, $end_date,  $page_id, WebhookDataInstapage::View);

            $InstaPage = InstaPage::where(InstaPage::PageId,$page_id)->first();
            $filename = !empty($page_id) ? sprintf("%s.xls", $InstaPage['page_name']) : '';

            if (!empty(request('download-csv'))) {
                // data for Excel
                $ExcelData = WebhookDataInstapage:: getInstaPageList($start_date, $end_date,  $page_id, WebhookDataInstapage::Excel);
                $counter = 0;
                foreach($ExcelData as $data){
                    foreach($InstaPage['lead_fields'] as $page => $key ){
                        $keys[] = $key;
                        $excel_data[$counter][$key] = $data['data']['body'][$key];
                    }
                    $counter ++;
                }
                return Excel\Facades\Excel::download(new InstaLeadsExport($excel_data), $filename);
            }
        }

        session()->flashInput(request()->input());

        return view('pages.leads',
            [  'Pages'=>$Pages,
               'data' => $LeadsData,
               'fields' => $InstaPage,
               'breadcrumb' => $breadcrumb,
               'param' => request()->method()
            ]);
    }
}
