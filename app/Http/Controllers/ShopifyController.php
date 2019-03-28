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

class ShopifyController extends BaseController
{
    public function ShopifyBulkUpload()
    {

        return view('orders-bulk-upload');
    }

    public function ShopifyBulkUpload_result(Request $request)
    {
        $client = new Client();
        $api_key = env('SHOPIFY_APIKEY');
        $api_pass = env('SHOPIFY_PASSWORD');
        $api_store = env('SHOPIFY_STORE');
        $url =sprintf("https://%s:%s@%s.myshopify.com/admin/orders.json",$api_key,$api_pass,$api_store);
        $orderdata = array(
            'order' => array(
                'line_items' => array(
                    0 => array(
                        'title' => 'Trip to Amsterdam',
                        'quantity' => 1,
                        'variant_id' => 'EDS-AMS',
                        'vendor'=>'Valedra',
                        'product_id'=>9043955845
                    ),
                )
            )
        );
        var_dump($orderdata);
        $order_data = json_encode($orderdata);
        $request = $client->post($url,['form_params'=>$order_data]);
        $response = $request->getBody()->getContents();
        print_r($response);

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
                        $data['upload_date']= $request['date'];
//                        \DB::table('shopify_excel_upload')->insert($data);
                    }
                }
            }        }
            if (!empty($errored_data)){
                return view('bulkupload-preview')->with('errored_data',$errored_data)->with('excel_response',$excel_response);
            }
            else{
                $message='Thank You!Your file was successfully uploaded.';
                return view('orders-bulk-upload')->with('message',$message);
            }
        return view('orders-bulk-upload');
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

    private function create_customer()
    {
        $client = new Client();
        $api_key = env('SHOPIFY_APIKEY');
        $api_pass = env('SHOPIFY_PASSWORD');
        $api_store = env('SHOPIFY_STORE');
        $url =sprintf("https://%s:%s@%s.myshopify.com/admin/customers.json",$api_key,$api_pass,$api_store);
        $request = $client->post($url);
        $response = $request->getBody()->getContents();
        print_r($response);


    }

    public function create_order()
    {
        $client = new Client();
        $api_key = env('SHOPIFY_APIKEY');
        $api_pass = env('SHOPIFY_PASSWORD');
        $api_store = env('SHOPIFY_STORE');
        $url =sprintf("https://%s:%s@%s.myshopify.com/admin/orders.json",$api_key,$api_pass,$api_store);
        $orderdata = array(
            'order' => array(
                'line_items' => array(
                    0 => array(
                        'title' => 'Trip to Amsterdam',
                        'quantity' => 1,
                        'variant_id' => 'EDS-AMS',
                        'vendor'=>'Valedra',
                        'product_id'=>9043955845
                    ),
                )
            )
        );
        $request = $client->post($url);
        $response = $request->getBody()->getContents();
        print_r($response);

        return $response;
    }

}







































// Fetching uploaded file and moving it to a new destination
//             $file = $request->file('file');
//             $destinationPath = 'uploads';
//                	$file_moved = $file->move($destinationPath,$file->getClientOriginalName());
//                	$file_path = $file_moved->getRealPath();


