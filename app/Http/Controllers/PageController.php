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

            $LeadsData = WebhookDataInstapage::getInstaPageList($start_date, $end_date, $page_id,WebhookDataInstapage::View);
            $InstaPage = InstaPage::where(InstaPage::PageId,$page_id)->first();
            $filename = !empty($page_id) ? sprintf("%s.xls", $InstaPage['page_name']) : '';

            // When excel is requested
            if (!empty(request('download-csv'))) {
                $excel_data = [];
                // data for Excel
                $ExcelData = WebhookDataInstapage::getInstaPageList($start_date, $end_date, $page_id, WebhookDataInstapage::Excel);
                $counter = 0;
                foreach ($ExcelData as $data) {
                    foreach ($InstaPage['lead_fields'] as $page => $key) {
                        $keys[] = $key;
                        if ($key == 'Captured At') {
                            $excel_data[$counter]['Captured At'] = date("d-M-Y H:i:s", $data['created_at']);
                        } else {
                            $excel_data[$counter][$key] = $data['data']['body'][$key] ?? '';
                        }
                    }
                    $counter++;
                }

                if (!empty($excel_data)) {
                    return Excel\Facades\Excel::download(new InstaLeadsExport($excel_data), $filename);
                }
            }
        }

        session()->flashInput(request()->input());

        return view('pages.leads',
            [  'Pages'=>$Pages,
               'data' => $LeadsData,
               'fields' => $InstaPage,
               'breadcrumb' => $breadcrumb,
               'ExcelData' => empty($excel_data),
               'param' => request()->method()
            ]);
    }
}
