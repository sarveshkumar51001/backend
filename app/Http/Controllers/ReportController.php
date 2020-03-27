<?php


namespace App\Http\Controllers;

use App\Exports\ReportExport;
use App\Library\Shopify\Report;
use App\Models\ShopifyExcelUpload;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel;

Class ReportController extends BaseController
{

    public function main()
    {
        $order_data = [];
        $breadcrumb = ['Reports' => ''];

        $param = request()->has('daterange') ? true:false;

        $date_params = getStartEndDate(request('daterange'));
        [$start_date, $end_date] = $date_params;
        $Orders = ShopifyExcelUpload::where('uploaded_by', Auth::id())->whereBetween('payments.upload_date', [$start_date, $end_date])->get();
        $count = 0;

        foreach ($Orders as $Order) {

            $data = [
                'Sl. No.' => '',
                'School Code' => Report::getSchoolCode($Order->delivery_institution,$Order->branch),
                'Student Name' => $Order->student_first_name . " " . $Order->student_last_name,
                'Activity' => $Order->activity,
                'Class & Section' => $Order->class . " " . $Order->section
            ];

            if(head($Order['payments'])['mode_of_payment'] == ShopifyExcelUpload::$modesTitle[ShopifyExcelUpload::MODE_CHEQUE]
            || head($Order['payments'])['mode_of_payment'] == ShopifyExcelUpload::$modesTitle[ShopifyExcelUpload::MODE_DD]) {
                if (sizeof($Order['payments']) == 1) {
                    $data['Sl. No.'] = $count++;
                    $order_data[] = array_merge($data, [
                        'Drawer Account No.' => head($Order['payments'])['drawee_account_number'],
                        'MICR Code' => head($Order['payments'])['micr_code'],
                        'Instrument Type (Chq/DD)' => head($Order['payments'])['mode_of_payment'],
                        'Cheque/DD No.' => head($Order['payments'])['chequedd_no'],
                        'Cheque/DD Date' => head($Order['payments'])['chequedd_date'] ,
                        'Cheque/DD Amount' => head($Order['payments'])['amount'] ,
                        'Drawn On Bank' => head($Order['payments'])['bank_name']
                    ]);
                }
            }else{
                foreach ($Order->payments as $payment) {

                    if($payment['mode_of_payment'] == ShopifyExcelUpload::$modesTitle[ShopifyExcelUpload::MODE_CHEQUE]
                    || $payment['mode_of_payment'] == ShopifyExcelUpload::$modesTitle[ShopifyExcelUpload::MODE_DD]) {
                        $data['Sl. No.'] = $count++;
                        $order_data[] = array_merge($data, [
                            'Drawer Account No.' => $payment['drawee_account_number'],
                            'MICR Code' => $payment['micr_code'],
                            'Instrument Type (Chq/DD)' => $payment['mode_of_payment'],
                            'Cheque/DD No.' => $payment['chequedd_no'],
                            'Cheque/DD Date' => $payment['chequedd_date'] ,
                            'Cheque/DD Amount' => $payment['amount'] ,
                            'Drawn On Bank' => $payment['bank_name']
                        ]);
                    }
                }
            }
        }
        if(!empty(request('download-csv'))){
            return Excel\Facades\Excel::download(new ReportExport($order_data),'cheque_deposit_report.csv');
        }
        return view('shopify.reports-main',['breadcrumb' => $breadcrumb,'data' =>$order_data,'param' => $param]);
    }
}
