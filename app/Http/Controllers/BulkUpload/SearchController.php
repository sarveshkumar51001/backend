<?php

namespace App\Http\Controllers\BulkUpload;

use App\Http\Controllers\BaseController;
use App\Library\Shopify\Search;

class SearchController extends BaseController
{
    public function search()
    {
        $result = [];
        $breadcrumb = ['Search' => ''];
        $limit = 25;

        $query = request('qry');
        $school_name = request('school-name');
        $date = request('daterange');
        $mode = request('mode');

        if(empty($query) && empty($school_name) && empty($date) && empty($mode)){
            return view('shopify.bulkupload-search',['breadcrumb' => $breadcrumb,'Noquery'=>[]]);
        }

        $result['students'] = Search::Students($query,$school_name,$limit);
        $result['orders'] = Search::Orders($query,$school_name,$date,$mode,$limit);

        return view('shopify.bulkupload-search', ['breadcrumb' => $breadcrumb,'result' =>$result, 'query'=>request('qry')]);
    }
}

