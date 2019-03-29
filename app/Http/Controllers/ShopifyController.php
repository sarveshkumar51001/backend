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
use PHPShopify;

class ShopifyController extends BaseController
{
    public function ShopifyBulkUpload()
    {

        return view('orders-bulk-upload');
    }

    public function ShopifyBulkUpload_result(Request $request)
    {
        if ($request->file('file')) {

            config(['excel.import.startRow' => 2, 'excel.import.heading' => 'slugged_with_count']);
            $path = $request->file('file')->getRealPath();

            $shopify_data = Excel::load($path, function ($reader) {
            })->get()->first();

            $errored_data = [];
            $excel_response = [];
            foreach ($shopify_data as $data) {
                $data = $data->toArray();

                if (array_filter($data)) {
                    $excel_read_response = $this->data_validate($data);

                    if (!empty($excel_read_response)) {
                        $errored_data[] = $data;
                        $excel_response[] = $excel_read_response;
                    } else {
                        $valid_data[] = $data;
                        $data['upload_date'] = $request['date'];
                        \DB::table('shopify_excel_upload')->insert($data);
                    }
                }
            }
        }


        $config = array(
            'ShopUrl' => 'valedra-test.myshopify.com',
            'ApiKey' => env('SHOPIFY_APIKEY'),
            'Password' => env('SHOPIFY_PASSWORD'),
        );

        PHPShopify\ShopifySDK::config($config);

        $shopify = new PHPShopify\ShopifySDK;

        foreach($valid_data as $excel_data)
        $customers = $shopify->Customer->search("phone:9514254601");
        if (empty($customers)) {
            $customer_data = array(
                "customer" => array(
                    "first_name" => "Rohan",
                    "last_name" => "Raja",
                    "email" => "rohan.raja@example.com",
                    "phone" => "9514254601",
                    "verified_email" => true,
                    "addresses" => [[
                        "address1" => "23,Institutional Area,Sector 31",
                        "city" => "Gurugram",
                        "province" => "Haryana",
                        "zip" => "110074",
                        "country" => "India"
                    ]]));
            $shopify->Customer->post($customer_data);
        }
        else
            $order_data = array (
                "email" => "foo@example.com",
                "fulfillment_status" => "unfulfilled",
                "line_items" => [
                    [
                        "variant_id" => 25542354174016,
                        "quantity" => 1
                    ]
                ]
            );
            $shopify->Order->post($order_data);

        if (!empty($errored_data)) {
            return view('bulkupload-preview')->with('errored_data', $errored_data)->with('excel_response', $excel_response);
        } else {
            $message = 'Thank You!Your file was successfully uploaded.';
            return view('orders-bulk-upload')->with('message', $message);
        }
    }

    private function data_validate($data_array)
    {
        $rules = [
            "shopify_activity_id" => "required",
            "school_name" => "required|string",
            "school_enrollment_no" => "required",
            "activity" => "required",
            "mobile_number" => "required|regex:/^[0-9]{10}$/",
            "email_id" => "required|email"
        ];

        $validator = Validator::make($data_array, $rules);

        return $validator->getMessageBag()->toArray();

    }

}
