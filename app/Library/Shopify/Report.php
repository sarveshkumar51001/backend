<?php

namespace App\Library\Shopify;

use App\Models\ShopifyExcelUpload;
use App\User;
use Illuminate\Support\Facades\Auth;

class Report
{
    const CHEQUE_REPORT_KEYS = ['Sl. No.','School Code','Student Name','Activity','Class & Section','Drawer Account No.',
        'MICR Code','Instrument Type (Chq/DD)','Cheque/DD No.','Cheque/DD Date','Cheque/DD Amount','Drawn On Bank'];


    const REPORT_MAPPING = [
        "1" => ['name' => "Bank Cheque Deposit Report" , 'keys' => self::CHEQUE_REPORT_KEYS]
        //
        //
        //
        //
    ];

    public static function ValidateLocation($delivery_institution, $branch){
    	if (empty($delivery_institution) || empty($branch)) {
    		return false;
	    }

        return isset(ShopifyExcelUpload::SCHOOL_ADDRESS_MAPPING[$delivery_institution][$branch]);
    }

	public static function ValidateLocationAccess($delivery_institution, $branch){
		if (empty($delivery_institution) || empty($branch)) {
			return false;
		}

		$user_access = ShopifyExcelUpload::SCHOOL_ADDRESS_MAPPING[$delivery_institution][$branch]['access'] ?? [];

		return in_array(Auth::user()->email, $user_access);
	}

    public static function getSchoolCode($delivery_institution, $branch)
    {
        if (array_key_exists($delivery_institution, ShopifyExcelUpload::SCHOOL_ADDRESS_MAPPING)) {
            $locations = ShopifyExcelUpload::SCHOOL_ADDRESS_MAPPING[$delivery_institution];
            if (array_key_exists($branch, $locations)) {
                return $locations[$branch]['code'];
            }
        }
        return " " ;
    }

	/**
	 * @param $start
	 * @param $end
	 * @param $delivery_institution
	 * @param $branch
	 *
	 * @return array
	 */
    public static function getBankChequeDepositData($start, $end, $delivery_institution = '', $branch = '')
    {
        $Orders = ShopifyExcelUpload::whereBetween('payments.upload_date', [$start, $end]);

        if ($delivery_institution != '-1') {
	        $Orders->where('delivery_institution', $delivery_institution)
	               ->where('branch', $branch);
        }

        $Orders = $Orders->get();

        $count = 1;
        $order_data = [];

        foreach ($Orders as $Order) {

            $data = [
                'Sl. No.' => '',
                'School Code' => Report::getSchoolCode($Order->delivery_institution,$Order->branch),
                'Student Name' => $Order->student_first_name . " " . $Order->student_last_name,
                'Activity' => $Order->activity,
                'Class & Section' => $Order->class . " " . $Order->section
            ];

                if (sizeof($Order['payments']) == 1) {

                    if (head($Order['payments'])['mode_of_payment'] == ShopifyExcelUpload::$modesTitle[ShopifyExcelUpload::MODE_CHEQUE]
                        || head($Order['payments'])['mode_of_payment'] == ShopifyExcelUpload::$modesTitle[ShopifyExcelUpload::MODE_DD]) {

                        $data['Sl. No.'] = $count++;
                        $order_data[] = array_merge($data, [
                            'Drawer Account No.' => head($Order['payments'])['drawee_account_number'],
                            'MICR Code' => head($Order['payments'])['micr_code'],
                            'Instrument Type (Chq/DD)' => head($Order['payments'])['mode_of_payment'],
                            'Cheque/DD No.' => head($Order['payments'])['chequedd_no'],
                            'Cheque/DD Date' => head($Order['payments'])['chequedd_date'],
                            'Cheque/DD Amount' => head($Order['payments'])['amount'],
                            'Drawn On Bank' => head($Order['payments'])['bank_name']
                        ]);
                    }
                } else{
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
        return $order_data;
    }




























}
