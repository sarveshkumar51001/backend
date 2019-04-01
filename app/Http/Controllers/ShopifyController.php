<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use GuzzleHttp\Client;
use function GuzzleHttp\json_encode;
use MongoDB\Client as Mongo;
use Maatwebsite\Excel\Facades\Excel;
use Jenssegers\Mongodb\Eloquent\Model as Eloquent;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Input;
use PhpOption\None;
use PHPShopify;
use App\User, Socialite, Auth, Exception;
use App\Jobs\ShopifyOrderCreation;

class ShopifyController extends BaseController
{
    public function ShopifyBulkUpload()
    {

        return view('orders-bulk-upload');
    }

    public function ShopifyBulkUpload_result(Request $request)
    {
        # Configuring Laravel Excel for skipping header row and modifiying the duplicate header names
        config(['excel.import.startRow' => 2, 'excel.import.heading' => 'slugged_with_count']);

        # Getting the file path
        $path = $request->file('file')->getRealPath();

        #Loading the excel file
        $shopify_data = Excel::load($path, function ($reader) {
        })->get()->first();

        $file_id = uniqid('shopify_');
        $errored_data = [];
        $excel_response = [];

        foreach ($shopify_data as $data) {

            $data = $data->toArray();

            if (array_filter($data)) {

                $excel_read_response = $this->data_validate($data);

                if (!empty($excel_read_response)) {

                    $excel_response[] = $excel_read_response;
                    $errored_data[] = $data;
                }
            }
        }
        # Inserting data in MongoDB
        if (empty($errored_data)) {
            $flag =1;
            $data['upload_date'] = $request['date'];
            $data['uploaded_by']= Auth::user()->name;
            $data['file_id'] = $file_id;

//          \DB::table('shopify_excel_upload')->insert($data);
            return view('orders-bulk-upload')->with('flag', $flag);
            }
        # Returning view with errored rows and message.
        else {
            return view('bulkupload-preview')->with('errored_data', $errored_data)->with('excel_response', $excel_response);
        }
    }

    private function data_validate($data_array)
    {
        $rules = [
            "shopify_activity_id" => "required|string",
            "school_name" => "required|string",
            "school_enrollment_no" => "required",
            "mobile_number" => "required|regex:/^[0-9]{10}$/",
            "email_id" => "required|email"
        ];

        $validator = Validator::make($data_array, $rules);

        return $validator->getMessageBag()->toArray();

    }

}
