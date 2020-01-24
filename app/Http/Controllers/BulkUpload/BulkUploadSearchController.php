<?php

namespace App\Http\Controllers\BulkUpload;

use App\Http\Controllers\BaseController;
use App\Library\Shopify\Search;

class BulkUploadSearchController extends BaseController
{
    public function search()
    {
        $result = [];
        $breadcrumb = ['Search' => ''];
        $limit = 25;

        $result['students'] = Search::Students(request('qry'),request('school-name'),$limit);
        $result['orders'] = Search::Orders(request('qry'),request('school-name'),request('date'),request('mode'),$limit);

        return view('shopify.bulkupload-search', ['breadcrumb' => $breadcrumb,'result' =>$result, 'query'=>request('qry')]);
    }
}

