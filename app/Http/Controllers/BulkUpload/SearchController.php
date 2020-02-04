<?php

namespace App\Http\Controllers\BulkUpload;

use App\Http\Controllers\BaseController;
use App\Library\Shopify\Search;

class SearchController extends BaseController
{
    public function search()
    {
        $result = [];
        $limit = 25;
        $breadcrumb = ['Search' => ''];

        $query = request('qry');
        $mode = request('mode');
        $date = request('search_daterange');
        $school_name = request('school-name');

        $request = (request()->has('search_daterange') ? true : false);

        if($date || $query || $mode || $school_name){
            $result['students'] = Search::Students($query,$school_name,$limit);
            $result['orders'] = Search::Orders($query,$school_name,$date,$mode,$limit);
        }

        return view('shopify.bulkupload-search', ['breadcrumb' => $breadcrumb,'result' =>$result,'request' => $request, 'query'=>request('qry')]);
    }
}

