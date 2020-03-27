<?php

namespace App\Library\Shopify;

use App\Models\ShopifyExcelUpload;

class Report
{

    const REPORT_NAME_MAPPING = [
        "1" => "Bank Cheque Deposit Report",
        //
        //
        //
        //
    ];

    public static function getSchoolCode($delivery_institution,$branch)
    {
        if (array_key_exists($delivery_institution, ShopifyExcelUpload::SCHOOL_ADDRESS_MAPPING)) {
            $locations = ShopifyExcelUpload::SCHOOL_ADDRESS_MAPPING[$delivery_institution];
            if (array_key_exists($branch, $locations)) {
                return $locations[$branch]['code'];
            }
        }
        return " " ;
    }




























}
