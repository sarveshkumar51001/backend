<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use MongoDB\Client as Mongo;
use Maatwebsite\Excel\Facades\Excel;

class ShopifyController extends BaseController
{
    public function ShopifyBulkUpload()
    {

        return view('orders-bulk-upload');
    }

    public function ShopifyBulkUpload_result(Request $request)
    {
        {

            $m = new Mongo("mongodb://root:sAR8saWFRypb@13.127.152.118:27017");
            $db = $m->backend;
            $collection = $db->shopify_excel_upload;



            // Fetching uploaded file and moving it to a new destination
            // $file = $request->file('file');
            // $destinationPath = 'uploads';
            //    	$file_moved = $file->move($destinationPath,$file->getClientOriginalName());
            //    	$file_path = $file_moved->getRealPath();

            if ($request->file('file')) {

                config(['excel.import.startRow' => 2, 'excel.import.heading' => 'slugged_with_count']);
                $path = $request->file('file')->getRealPath();

                $shopify_data = Excel::load($path, function ($reader) {
                })->get()->first();



                foreach ($shopify_data as $data ) {
                    $data = $data->toArray();

                    if (!empty($data["shopify_activity_id"])) {
                       $collection->insertOne($data);
                    }
                }
            }
        }

        return view('orders-bulk-upload');
    }
}
