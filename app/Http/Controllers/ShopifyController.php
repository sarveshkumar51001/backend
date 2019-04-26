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
use MongoDB\Driver\Exception\BulkWriteException;
use PhpOption\None;
use PHPShopify;
use App\User, Socialite, Auth, Exception;
use App\Jobs\ShopifyOrderCreation;
use App\Models\Shopify;
use Illuminate\Support\Carbon;

class ShopifyController extends BaseController
{
    public function ShopifyBulkUpload()
    {

//        $config = array(
//            'ShopUrl' => 'valedra-test.myshopify.com',
//            'ApiKey' => env('SHOPIFY_APIKEY'),
//            'Password' => env('SHOPIFY_PASSWORD'));
//
//        PHPShopify\ShopifySDK::config($config);
//
//        $shopify = new PHPShopify\ShopifySDK;
//
//        $order_data = [
//                "currency"=> "USD",
//                "amount"=> 20.00,
//                "kind"=> "sale"
//            ];
//
//        $shopify->Order(1007884042304)->Transaction->post($order_data);

        return view('orders-bulk-upload');
    }

    public function ShopifyBulkUpload_result(Request $request)
    {

        # Configuring Laravel Excel for skipping header row and modifying the duplicate header names
        try {
            config(['excel.import.startRow' => 2, 'excel.import.heading' => 'slugged_with_count']);

            # Fetching uploaded file and moving it to a destination specific for a user.
            $name = sprintf("%s_%s", Auth::user()->name, Auth::user()->id);
            $user_name = preg_replace('/\s+/', '_', $name);

            if (!is_dir($user_name)) {
                mkdir($user_name);
            }
            // Extracting file from Post request
            $excel_file = $request->file('file');
            $excel_path = $excel_file->getClientOriginalName();# Getting original client name
            $excel_file_path = $excel_path . '_' . time() . '.xlsx'; # Adding current timestamp to file
            $user_path = public_path($user_name);# public path for file storage
            $path = $excel_file->move($user_path, $excel_file_path); # Moving uploaded excel file to specific folder
            $real_path = $path->getRealPath(); # Getting real path

            # Loading the excel file
            $rows = Excel::load($real_path, function ($reader) {
            })->get()->first();

            $file_id = uniqid('shopify_');
            $errored_data = $excel_response = $valid_data = $slice_array = [];
            $pattern = '/_[1-9]$/';

            foreach ($rows as $data) {
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
                        $data['order_id'] = "";
                        $date = $data['date_of_enrollment'];
                        $data['date_of_enrollment'] = Carbon::createFromFormat('Y-m-d H:i:s', $date)->format('d.m.Y');

                        # Making chunk of installments from the flat array
                        $offset_array = array(32, 43, 54, 65, 76);
                        $final_slice = [];
                        foreach ($offset_array as $offset_value) {
                            $slice = array_slice($data, $offset_value, 11);
                            foreach ($slice as $key => $value) {
                                $pattern = '/(.+)(_[\d]+)/i';
                                $replacement = '${1}';
                                $new_key = preg_replace($pattern, $replacement, $key);
                                $new_slice[$new_key] = $value;
                            }
                            $new_slice['processed'] = 'No';
                            array_push($final_slice, $new_slice);
                        }
                        $i = 1;
                        foreach ($final_slice as $slice) {
                            $slice_array[$i++] = $slice;
                        }
                        $data['installments'] = $slice_array;

                        # Removing slugged with count keys from the array
                        foreach ($data as $key => $value) {
                            if (preg_match($pattern, $key)) {
                                unset($data[$key]);
                            }
                        }
                        # Removing unwanted keys
                        $unwanted_keys = array('installment_amount', 'pdc_collectedpdc_to_be_collectedstatus', 'cheque_no', 'chequeinstallment_date', '0');
                        foreach ($unwanted_keys as $keys) {
                            unset($data[$keys]);
                        }
                        $valid_data[] = $data;
                    }
                }
            }

            $amount_collected_cash = $request["cash-total"];
            $amount_collected_cheque = $request["cheque-total"];
            $amount_collected_online = $request["online-total"];

            $amount_data = $this->amount_validation($valid_data); # Calling function for validating amount data

            if ($amount_collected_cash != $amount_data[0]) {
                $flag_msg = Shopify::STATUS_CASH_FAILURE;
            } elseif ($amount_collected_cheque != $amount_data[1]) {
                $flag_msg = Shopify::STATUS_CHEQUE_FAILURE;
            } elseif ($amount_collected_online != $amount_data[2]) {
                $flag_msg = Shopify::STATUS_ONLINE_FAILURE;
            }

            # Inserting data to MongoDB after validation
            if (empty($errored_data)) {
                $flag_msg = Shopify::STATUS_SUCCESS;

                foreach($valid_data as $valid_row){

                    $date_enroll = $valid_row['date_of_enrollment'];
                    $activity_id = $valid_row['shopify_activity_id'];
                    $std_enroll_no = $valid_row['school_enrollment_no'];

                    $installment_doc = \DB::table('shopify_excel_upload')->where('date_of_enrollment',$date_enroll)->where('shopify_activity_id',$activity_id)->where('school_enrollment_no',$std_enroll_no)->get()->first();

                    if (empty($installment_doc)){
                        \DB::table('shopify_excel_upload')->insert($valid_row);
                    }
                    else{
                        $doc_id = $installment_doc["_id"];
                        $installment_data = $installment_doc["installments"];
                        $excel_installment_data = $valid_row["installments"];

                        for($i=1;$i<=5;$i++){

                            if(empty(array_filter($installment_data[$i]))){
                                $installment_data[$i] = $excel_installment_data[$i];
                            }
                        }
                        $updatedetails = [
                            'installments' => $installment_data,
                            'job_status' => 'pending'
                        ];
                        \DB::table('shopify_excel_upload')->where('_id',$doc_id)->update($updatedetails);
                    }
                }
                $post_data = \DB::table('shopify_excel_upload')->where('job_status', 'failed')->orWhere('job_status', 'pending')->get();

                foreach ($post_data as $info)

//                    ShopifyOrderCreation::dispatch($info);

                return view('orders-bulk-upload')->with('flag_msg', $flag_msg);
            } else {
                return view('bulkupload-preview')->with('errored_data', $errored_data)->with('excel_response', $excel_response);
            }
        } catch (BulkWriteException $bulk) {
            return view('UploadError');
        } catch (Exception $e) {
            abort(500);
        }

        return view('orders-bulk-upload');
    }

    private function data_validate($data_array)
    {
        $rules = [
            "shopify_activity_id" => "required|numeric",
            "school_name" => "required|string",
            "school_enrollment_no" => "required",
            "mobile_number" => "required|regex:/^[0-9]{10}$/",
            "email_id" => "required|email"
        ];

        $validator = Validator::make($data_array, $rules);

        return $validator->getMessageBag()->toArray();
    }

    private function amount_validation($file)
    {
        $installment_amount_array = [];
        $cash_array = [];
        $cheque_array = [];
        $online_array = [];

        foreach ($file as $row) {

            for ($i = 1; $i <= 5; $i++) {
                $amount = $row["installments"][$i]["installment_amount"];
                array_push($installment_amount_array, $amount);
            }

            if (empty(array_filter($installment_amount_array))) {
                if ($row["mode_of_payment"] == 'Cash') {
                    $cash = $row["final_fee_incl_gst"];
                    array_push($cash_array, $cash);
                } elseif ($row["mode_of_payment"] == 'Cheque') {
                    $cheque = $row["final_fee_incl_gst"];
                    array_push($cheque_array, $cheque);
                } else {
                    $online = $row["final_fee_incl_gst"];
                    array_push($online_array, $online);
                }
            } else {
                for ($i = 1; $i <= 5; $i++) {
                    if ($row["installments"][$i]["mode_of_payment"] == 'Cash') {
                        $installment_cash = $row["installments"][$i]["installment_amount"];
                        array_push($cash_array, $installment_cash);
                        array_push($cash_array,$row["registration_amount"]);
                    } elseif ($row["installments"][$i]["mode_of_payment"] == 'Cheque') {
                        $installment_cheque = $row["installments"][$i]["installment_amount"];
                        array_push($cheque_array, $installment_cheque);
                        array_push($cheque_array,$row["registration_amount"]);
                    } else {
                        $installment_online = $row["installments"][$i]["installment_amount"];
                        array_push($online_array, $installment_online);
                        array_push($online_array,$row["registration_amount"]);
                    }
                }
            }
        }

        $total_cash_amount = array_sum($cash_array);
        $total_cheque_amount = array_sum($cheque_array);
        $total_online_amount = array_sum($online_array);

        $amounts_array = array($total_cash_amount, $total_cheque_amount, $total_online_amount);

        return $amounts_array;

    }

    public function List_All_Files(){

        $name = sprintf("%s_%s", Auth::user()->name, Auth::user()->id);
        $user_name = preg_replace('/\s+/', '_', $name);

        $dir = sprintf('/var/www/html/htdocs/backend/public/%s/',$user_name);

        foreach (scandir($dir) as $file) {
            if ('.' === $file) continue;
            if ('..' === $file) continue;

            $exp_file_name = explode("_", $file)[3];
            $unix_time = (int)explode(".",$exp_file_name)[0];
            $upload_date = date('Y-m-d',$unix_time);

            $file = sprintf("%s/%s",$user_name,$file);

            $files[$upload_date] = $file;
        }

        return view( 'past-files-upload')->with('files',$files);
    }

    public function List_All_Orders()
    {
        $records_array = [];
        $auth_name = Auth::user()->name;

        $mongodb_record = \DB::table('shopify_excel_upload')->where('uploaded_by',$auth_name)->Where('job_status','completed')->get();

        foreach ($mongodb_record as $records)

            array_push($records_array,$records);

        return view('previous-orders')->with('records_array',$records_array);
    }

}


