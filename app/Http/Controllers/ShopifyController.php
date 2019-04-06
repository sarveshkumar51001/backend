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
        # Configuring Laravel Excel for skipping header row and modifying the duplicate header names

        try {
            config(['excel.import.startRow' => 2, 'excel.import.heading' => 'slugged_with_count']);

            # Fetching uploaded file and moving it to a new destination
//            $excel_file = $request->file('file');
//            $destinationPath = 'uploads';
//            $excel_file->move($destinationPath,$excel_file->getClientOriginalName());

            $path = $request->file('file')->getRealPath();

            #Loading the excel file
            $shopify_data = Excel::load($path, function ($reader) {
            })->get()->first();

            $file_id = uniqid('shopify_');
            $errored_data = [];
            $excel_response = [];
            $valid_data = [];
            $slice_array = [];
            $pattern = '/_[1-9]$/';

            foreach ($shopify_data as $data) {

               $data = $data->toArray();

               # Removing unwanted columns
                foreach ($data as $key => $value) {
                    if (strpos($key, '_') === 0) {
                        unset($data[$key]);
                    }
                }

                if (array_filter($data)) {

                    $excel_read_response = $this->data_validate($data);

                    if (!empty($excel_read_response)) {

                        $excel_response[] = $excel_read_response;
                        $errored_data[] = $data;
                    } else {
                        $data['upload_date'] = $request['date'];
                        $data['uploaded_by'] = Auth::user()->name;
                        $data['file_id'] = $file_id;
                        $data['job_status'] = "pending";

                        # Making chunk of installments from the array
                        $offset_array = array(32, 43, 54, 65, 76);
                        foreach ($offset_array as $offset_value) {
                            $slice = array_slice($data, $offset_value, 11);
                            foreach ($slice as $key => $value) {
                                $pattern = '/(.+)(_[\d]+)/i';
                                $replacement = '${1}';
                                $new_key = preg_replace($pattern, $replacement, $key);
                                $new_slice[$new_key] = $value;
                            }
                            for ($i =1; $i<=env('INSTALLMENT_NUMBER'); $i++){
                                $key_name = sprintf("installment_%s",$i);
                                $slice_array[$key_name] = $new_slice;

                        }$new_slice = array();
                        }
                        $data['installments'] = $slice_array;

                        # Removing slugged with count keys from the array
                        foreach ($data as $key => $value){
                            if(preg_match($pattern, $key)) {
                                unset($data[$key]);
                            }
                        }
                        # Removing unwanted keys
                        $unwanted_keys = array('installment_amount','pdc_collectedpdc_to_be_collectedstatus','cheque_no','chequeinstallment_date','0');
                        foreach($unwanted_keys as $keys) {
                            unset($data[$keys]);
                        }
                        $valid_data[] = $data;
                    }
                }
            }
            # Inserting data to MongoDB after validation

            if (empty($errored_data)) {
                $flag = 1;
                dd($valid_data);
                \DB::table('shopify_excel_upload')->insert($valid_data);

//                $mongo_data = \DB::table('shopify_excel_upload')->get()->first();

//                 ShopifyOrderCreation::dispatch($mongo_data);

                return view('orders-bulk-upload')->with('flag', $flag);
            } else {
                return view('bulkupload-preview')->with('errored_data', $errored_data)->with('excel_response', $excel_response);
            }
        } catch (\Exception $e) {
            Log::error($e);
            dd($e);
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
