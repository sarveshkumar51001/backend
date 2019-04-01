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

//        $config = array(
//            'ShopUrl' => 'valedra-test.myshopify.com',
//            'ApiKey' => env('SHOPIFY_APIKEY'),
//            'Password' => env('SHOPIFY_PASSWORD'));
//
//        PHPShopify\ShopifySDK::config($config);
//
//        $shopify = new PHPShopify\ShopifySDK; # new instance of PHPShopify class
//
////        $customers = $shopify->Customer->search("phone".":".$this->data["mobile_number"]);
//        $customers = $shopify->Customer->search("email:foo@example.com OR phone:9514254601");
//        dd($customers);

        # Configuring Laravel Excel for skipping header row and modifiying the duplicate header names

        try {
            config(['excel.import.startRow' => 2, 'excel.import.heading' => 'slugged_with_count']);

            # Getting the file path
            $path = $request->file('file')->getRealPath();

            #Loading the excel file
            $shopify_data = Excel::load($path, function ($reader) {
            })->get()->first();

            $file_id = uniqid('shopify_');
            $errored_data = [];
            $excel_response = [];
            $valid_data = [];

            foreach ($shopify_data as $data) {

                $data = $data->toArray();

                if (array_filter($data)) {

                    $excel_read_response = $this->data_validate($data);

                    if (!empty($excel_read_response)) {

                        $excel_response[] = $excel_read_response;
                        $errored_data[] = $data;
                    } else {
                        $data['upload_date'] = $request['date'];
                        $data['uploaded_by'] = Auth::user()->name;
                        $data['file_id'] = $file_id;
                        $valid_data[] = $data;
                    }
                }
            }
            # Inserting data to MongoDB after validation

            if (empty($errored_data)) {
                $flag = 1;
                \DB::table('shopify_excel_upload')->insert($valid_data);
                return view('orders-bulk-upload')->with('flag', $flag);
            } else {
                return view('bulkupload-preview')->with('errored_data', $errored_data)->with('excel_response', $excel_response);
            }
        } catch (\Exception $e) {
            Log::error($e);
            abort(500);
        }
        return view('orders-bulk-upload');
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
