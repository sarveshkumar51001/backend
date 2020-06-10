<?php

namespace App\Http\Controllers\BulkUpload;

ini_set('precision', 20); // Fix for long integer converting to exponential number Ref:https://github.com/Maatwebsite/Laravel-Excel/issues/1384#issuecomment-362059935

use App\Http\Controllers\BaseController;
use App\Library\Collection\Collection;
use App\Library\Permission;
use App\Models\ShopifyExcelUpload;
use App\Models\Upload;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Jobs\ShopifyOrderCreation;
use Maatwebsite\Excel\Facades\Excel;
use App\Library\Shopify\ExcelValidator;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use App\Library\Shopify\DB;
use PHPExcel;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Input;
use Exception\PHPExcel_Exception;
use App\Imports\ShopifyOrdersImport;
use Maatwebsite\Excel\HeadingRowImport;
use Maatwebsite\Excel\Imports\HeadingRowFormatter;


class ShopifyController extends BaseController
{
    public function upload()
    {
        $breadcrumb = ['Shopify' => route('bulkupload.previous_orders'), 'New Upload' => ''];

        return view('shopify.orders-bulk-upload')
            ->with('breadcrumb', $breadcrumb);
    }

    /**
     * @param Request $request
     *
     * @return $this
     * @throws \Exception
     */
    public function upload_preview(Request $request)
    {
        $rules = [
            'file' => 'mimes:xls|max:3072',
            'date' => [
                "required",
                "regex:" . ShopifyExcelUpload::DATE_REGEX
            ],
            'cash-total' => 'numeric',
            'cheque-total' => 'numeric',
            'online-total' => 'numeric'
        ];

        Validator::make($request->all(), $rules)->validate();

        $breadcrumb = ['Shopify' => route('bulkupload.previous_orders'), 'Upload Preview' => ''];

        # Configuring Laravel Excel for skipping header row and modifying the duplicate header names
        config([
            'excel.import.startRow' => 2,
            'excel.import.heading' => 'slugged_with_count',
            'excel.import.dates.enabled' => false,
            'excel.import.force_sheets_collection' => true
        ]);

        HeadingRowFormatter::default('shopify_bulk_upload');

        # Fetching uploaded file and moving it to a destination specific for a user.
        $name = sprintf("%s_%s", Auth::user()->name, Auth::user()->id);
        $user_name = preg_replace('/\s+/', '_', $name);

        // Making a directory for each unique user
        /*if (!is_dir($user_name)) {
            mkdir($user_name);
        }*/

        // Extracting file from Post request
        $excel_file = $request->file('file');

        // Adding current timestamp to file
        $originalFileName = $excel_file->getClientOriginalName();
        $fileName = time() . '_' . $originalFileName;
        $filePath = storage_path('uploads/' . $user_name);
        $path = $excel_file->move($filePath, $fileName);
        $file_id = 'shopify-' . crc32(uniqid()); # Unique identifier for the documents belonging to a single file

        // Loading the excel file
        try {
            $rows = array_first(Excel::toArray(new ShopifyOrdersImport(), $path->getRealPath()));
            $headers = array_keys(array_first($rows));
        } catch (\Exception $e) {
            return back()->withErrors(['The uploaded file seems invalid. Please download the latest sample file.']);
        }

        // Create Excel Raw object
        if (empty($headers)) {
            return back()->withErrors(['No data was found in the uploaded file']);
        }

        $ExcelRaw = (new \App\Library\Shopify\Excel($headers, $rows, [
            'upload_date' => $request['date'],
            'uploaded_by' => Auth::user()->id,
            'file_id' => $file_id,
            'job_status' => ShopifyExcelUpload::JOB_STATUS_PENDING,
            'order_id' => 0,
            'customer_id' => 0
        ]));

        // Format data
        $formattedData = $ExcelRaw->GetFormattedData();

        $metadata = [
            'cash-total' => request("cash-total"),
            'cheque-total' => request("cheque-total"),
            'online-total' => request("online-total")
        ];

        $metadata['total'] = array_sum($metadata);

        // Run the validation
        $errors = (new ExcelValidator($ExcelRaw, [
            'cash-total' => $metadata["cash-total"],
            'cheque-total' => $metadata["cheque-total"],
            'online-total' => $metadata["online-total"]
        ]))->Validate();

        // EVERYTHING LOOKS GOOD TO GO.......
        if (empty($errors)) {
            # Inserting data to MongoDB after validation
            $upsertList = [];
            foreach ($formattedData as $valid_row) {
                // Get the primary combination to lookup in database
                $date_enroll = $valid_row['date_of_enrollment'];
                $activity_id = $valid_row['shopify_activity_id'];
                $std_enroll_no = $valid_row['school_enrollment_no'];

                // Attempt to lookup in database with the key combination
                // Ex: 06/05/2019, VAL-12345-002, SS-1112
                $OrderRow = ShopifyExcelUpload::where('date_of_enrollment', $date_enroll)
                    ->where('shopify_activity_id', $activity_id)
                    ->where('school_enrollment_no', $std_enroll_no)
                    ->first();

                if (empty($OrderRow)) {

                    // Set PDC Payment Status to true if mode of payment is empty
                    foreach ($valid_row['payments'] as $index => $payment) {
                        if (empty($payment['mode_of_payment'])) {
                            $valid_row['payments'][$index]['is_pdc_payment'] = true;
                        }
                    }

                    $upsertList[] = $valid_row;
                } else {
                    $existingPaymentData = $OrderRow->payments;
                    // If there is any change in installments details provided in excel
                    foreach ($valid_row["payments"] as $index => $payment) {
                        /**
                         * Consider the payment data only if the payment is unprocessed
                         * Any update in already posted installments will be ignored
                         */
                        if ($existingPaymentData[$index]['processed'] == 'No') {
                            $existingPaymentData[$index] = $payment;
                        }

                        // Set PDC Payment Status to false if payment is received and vice versa.
                        if (!empty($existingPaymentData[$index]['mode_of_payment'])) {
                            $existingPaymentData[$index]['is_pdc_payment'] = false;
                        } else {
                            $existingPaymentData[$index]['is_pdc_payment'] = true;
                        }
                    }

                    // Reducing the payments array if there is any reduction in number of payments
                    $diff_element = array_diff_key($existingPaymentData, $valid_row["payments"]);
                    foreach ($diff_element as $key => $value) {
                        unset($existingPaymentData[$key]);
                    }

                    // Updating Order Data
                    $upsertList[] = [
                        'payments' => $existingPaymentData,
                        'errors' => [],
                        'job_status' => ShopifyExcelUpload::JOB_STATUS_PENDING,
                        '_id' => $OrderRow->_id
                    ];

                }
            }

            $metadata['new_order'] = $metadata['update_order'] = 0;

            $objectIDList = [];

            foreach ($upsertList as $document) {
                /**
                 * KEEP SEARCHABLE PAYMENTS BY SETTING THE KEYS IN ORDER
                 * We are resetting the keys for payments as first one might be empty
                 * So it will not appear in search hence we are resetting the array keys
                 */
                $document['payments'] = array_values(array_filter($document['payments']));

                if (empty($document['_id'])) {
                    $objectIDList[] = ShopifyExcelUpload::create($document)->id;
                    $metadata['new_order'] += 1;
                } else {
                    $_id = $document['_id'];
                    unset($document['_id']);
                    $metadata['update_order'] += 1;

                    // Update installment in database
                    ShopifyExcelUpload::where('_id', $_id)
                        ->update($document);

                    // Store the object id to be used to send document in job queue
                    $objectIDList[] = $_id;
                }
            }

            // Upload the file with metadata
            Upload::create([
                'user_id' => Auth::user()->id,
                'file_name' => $originalFileName,
                'file_id' => $file_id,
                'path' => $path->getRealPath(),
                'metadata' => $metadata,
                'status' => Upload::STATUS_SUCCESS,
                'type' => Upload::TYPE_SHOPIFY_ORDERS,
                'created_at' => time()
            ]);


            if (!empty($objectIDList)) {
                // Finally dispatch the data into queue for processing
                foreach (ShopifyExcelUpload::findMany($objectIDList) as $Object) {
                    ShopifyOrderCreation::dispatch($Object)->onQueue('low');
                }
            }
        }

        return view('shopify.bulkupload-preview')
            ->with('errored_data', $errors)
            ->with('excel_response', $formattedData)
            ->with('breadcrumb', $breadcrumb)
            ->with('headers', $ExcelRaw->GetFormattedHeader());
    }

    public function previous_uploads()
    {
        $breadcrumb = ['Shopify' => route('bulkupload.previous_orders'), 'Previous uploads' => ''];

        $Uploads = Upload::where('user_id', Auth::user()->id)->where('status', 'success')->orderBy('created_at', 'desc')->paginate(ShopifyExcelUpload::PAGINATE_LIMIT);

        return view('shopify.past-files-upload')->with('files', $Uploads)->with('breadcrumb', $breadcrumb);
    }

    public function location_wise_collection()
    {
        $start = Carbon::today()->startOfDay();
        $end = Carbon::today()->endOfDay();
        if (request('daterange')) {
            $range = explode(' - ', request('daterange'), 2);
            if (count($range) == 2) {
                $start = Carbon::createFromFormat('m/d/Y',$range[0]);
                $end = Carbon::createFromFormat('m/d/Y',$range[1]);
            }
        }
        $users = is_admin() ? [Auth::user()->email] : [];

        $Collection = new Collection();

        $data =  $Collection->setStart($start)
            ->setEnd($end)
            ->setUsers($users)
            ->setIsPDC(false)
            ->setBreakBy('branch')
            ->Get()
            ->toCSVFormat();

        $final = array();
        foreach($data as $doc) {
            if(isset($final[$doc['branch']])){
                $final[$doc['branch']]['amount'] += $doc['Amount'];
                $final[$doc['branch']]['order_count'] += $doc['Order Count'];
                $final[$doc['branch']]['txn_count'] += $doc['Txn Count'];
            } else{
                $final[$doc['branch']]['amount'] = $doc['Amount'];
                $final[$doc['branch']]['order_count'] = $doc['Order Count'];
                $final[$doc['branch']]['txn_count'] = $doc['Txn Count'];
            }
        }

        return $final;
    }

    public function previous_orders()
    {
        $revenue_data = $this->location_wise_collection();

        [$accessible_users,$teams] = Permission::has_access_to_users_teams();

        $date_params = GetStartEndDate(request('daterange'));
        [$start,$end] = $date_params;
        if ($start && $end) {
            if (request('filter') == 'team' && is_admin()) {
                $mongodb_records = ShopifyExcelUpload::whereBetween('payments.upload_date', [$start, $end])
                    ->paginate(ShopifyExcelUpload::PAGINATE_LIMIT)
                    ->appends(request()->query());
            } elseif (!empty($accessible_users)){
                if(!empty(\request('filter_user'))){
                    $mongodb_records = ShopifyExcelUpload::whereBetween('payments.upload_date', [$start, $end])
                        ->where('uploaded_by',\request('filter_user'))
                        ->paginate(ShopifyExcelUpload::PAGINATE_LIMIT)
                        ->appends(request()->query());
                } else {
                    $mongodb_records =ShopifyExcelUpload::whereBetween('payments.upload_date', [$start, $end])
                        ->orWhereIn('tag',$teams)
                        ->whereIn('uploaded_by',$accessible_users)
                        ->paginate(ShopifyExcelUpload::PAGINATE_LIMIT)
                        ->appends(request()->query());
                }
            } else {
                $mongodb_records = ShopifyExcelUpload::where('uploaded_by', Auth::user()->id)
                    ->whereBetween('payments.upload_date', [$start, $end])
                    ->paginate(ShopifyExcelUpload::PAGINATE_LIMIT)
                    ->appends(request()->query());
            }
        } else {
            $mongodb_records = ShopifyExcelUpload::where('uploaded_by', Auth::user()->id)->paginate(ShopifyExcelUpload::PAGINATE_LIMIT)->appends(request()->query());
        }

        $modeWiseData = [];
        foreach (ShopifyExcelUpload::$modesTitle as $mode => $title) {
            $modeWiseData[$mode]['count'] = $modeWiseData[$mode]['total'] = 0;
        }

        $successful_records = $mongodb_records->where('job_status', '!=', 'failed');

        foreach ($successful_records as $document) {
            foreach ($document['payments'] as $payment) {
                if (!empty($payment['upload_date']) && $payment['upload_date'] >= $start && $payment['upload_date'] <= $end) {
                    $mode = strtolower($payment['mode_of_payment']);
                    if (!empty($mode)) {
                        if ($mode == strtolower(ShopifyExcelUpload::$modesTitle[ShopifyExcelUpload::MODE_CHEQUE]) || $mode == strtolower(ShopifyExcelUpload::$modesTitle[ShopifyExcelUpload::MODE_DD])) {
                            if (!empty($payment['chequedd_date']) && Carbon::createFromFormat(ShopifyExcelUpload::DATE_FORMAT, $payment['chequedd_date'])->timestamp > time()) {
                                $modeWiseData[ShopifyExcelUpload::MODE_PDC]['total'] += $payment['amount'];
                                $modeWiseData[ShopifyExcelUpload::MODE_PDC]['count'] += 1;
                            }
                        }
                        if ($mode == strtolower(ShopifyExcelUpload::$modesTitle[ShopifyExcelUpload::MODE_CASH])) {
                            $modeWiseData[ShopifyExcelUpload::MODE_CASH]['total'] += $payment['amount'];
                            $modeWiseData[ShopifyExcelUpload::MODE_CASH]['count'] += 1;
                        } else if ($mode == strtolower(ShopifyExcelUpload::$modesTitle[ShopifyExcelUpload::MODE_CHEQUE])) {
                            $modeWiseData[ShopifyExcelUpload::MODE_CHEQUE]['total'] += $payment['amount'];
                            $modeWiseData[ShopifyExcelUpload::MODE_CHEQUE]['count'] += 1;
                        } else if ($mode == strtolower(ShopifyExcelUpload::$modesTitle[ShopifyExcelUpload::MODE_DD])) {
                            $modeWiseData[ShopifyExcelUpload::MODE_DD]['total'] += $payment['amount'];
                            $modeWiseData[ShopifyExcelUpload::MODE_DD]['count'] += 1;
                        } else if ($mode == strtolower(ShopifyExcelUpload::$modesTitle[ShopifyExcelUpload::MODE_ONLINE])) {
                            $modeWiseData[ShopifyExcelUpload::MODE_ONLINE]['total'] += $payment['amount'];
                            $modeWiseData[ShopifyExcelUpload::MODE_ONLINE]['count'] += 1;
                        } else if ($mode == strtolower(ShopifyExcelUpload::$modesTitle[ShopifyExcelUpload::MODE_PAYTM])) {
                            $modeWiseData[ShopifyExcelUpload::MODE_PAYTM]['total'] += $payment['amount'];
                            $modeWiseData[ShopifyExcelUpload::MODE_PAYTM]['count'] += 1;
                        } else if ($mode == strtolower(ShopifyExcelUpload::$modesTitle[ShopifyExcelUpload::MODE_NEFT])) {
                            $modeWiseData[ShopifyExcelUpload::MODE_NEFT]['total'] += $payment['amount'];
                            $modeWiseData[ShopifyExcelUpload::MODE_NEFT]['count'] += 1;
                        }
                    }
                }
            }
        }

        $breadcrumb = ['Shopify' => route('bulkupload.upload'), 'Previous orders' => ''];


        return view('shopify.previous-orders')
            ->with('records_array', $mongodb_records)
            ->with('breadcrumb', $breadcrumb)
            ->with('metadata', $modeWiseData)
            ->with('revenue_data',$revenue_data)
            ->with('accessible_users',$accessible_users);
    }

    public function download_previous($id)
    {
        $Uploads = Upload::find($id);

        $breadcrumb = ['Shopify' => route('bulkupload.upload'), 'Download' => ''];
        if ($Uploads['user_id'] == Auth::user()->id && file_exists($Uploads['path'])) {
            return response()->download($Uploads['path']);
        }

        return view('admin.404')->with('breadcrumb', $breadcrumb);
    }

    public function installments(Request $request) {

        $Post_Payment_Data = [];
        $post_payment = [];

        [$accessible_users,$teams] = Permission::has_access_to_users_teams();

        $start = 0;
        $end = time();
        if(!empty(request('daterange'))) {
            [$start,$end] = GetStartEndDate(request('daterange'));
        }

        if(!empty($accessible_users)){
            if(!empty(request('filter_user'))){
                $Post_Dated_Payments = DB::post_dated_payments()->where('uploaded_by',request('filter_user'))->get()->toArray();
            } else{
                $Post_Dated_Payments = DB::post_dated_payments()->whereIn('uploaded_by',$accessible_users)->get()->toArray();
            }
        } else {
            $Post_Dated_Payments = DB::post_dated_payments()->where('uploaded_by', Auth::id())->get()->toArray();
        }

        foreach($Post_Dated_Payments as $Payments) {

			$payment_array = $Payments['payments'];

			$post_payment_keys = array_keys(array_column($payment_array, 'is_pdc_payment'), true);
			foreach($post_payment_keys as $payment_key){

                $chequedd_timestamp = Carbon::createFromFormat(ShopifyExcelUpload::DATE_FORMAT, $payment_array[$payment_key]['chequedd_date'])->timestamp;

                if($chequedd_timestamp >= $start && $chequedd_timestamp <= $end) {
                    $post_payment['order_id'] = $Payments['order_id'];
                    $post_payment['file_id'] = $Payments['file_id'];
                    $post_payment['activity'] = $Payments['activity'];
                    $post_payment['activity_id'] = $Payments['shopify_activity_id'];
                    $post_payment['school_enrollment_no'] = $Payments['school_enrollment_no'];
                    $post_payment['student_name'] = $Payments['student_first_name'] . " " . $Payments['student_last_name'];
                    $post_payment['student_school'] = $Payments['school_name'] . ' , ' . $Payments['student_school_location'];
                    $post_payment['delivery_location'] = $Payments['delivery_institution'] . ' , ' . $Payments['branch'];
                    $post_payment['expected_date'] = $payment_array[$payment_key]['chequedd_date'];
                    $post_payment['expected_amount'] = $payment_array[$payment_key]['amount'];
                    $post_payment['owner'] = Permission::order_owner($Payments);

                    $Post_Payment_Data[] = $post_payment;
                }
			}
    	}
    	$Post_Payments = self::paginate_array($request, $Post_Payment_Data);
    	return view('shopify.installments')->with('collection_data',$Post_Payments)->with('users',$accessible_users);

    }

    public function paginate_array( Request $request,$data){

        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $collection = collect($data);
        $limit = ShopifyExcelUpload::PAGINATE_LIMIT;

        // Sort by expected date
        $collection = $collection->sortBy(function ($data) {
            return Carbon::createFromFormat(ShopifyExcelUpload::DATE_FORMAT, $data['expected_date'])->timestamp;
        });

        $currentPageItems = $collection->slice(($currentPage * $limit) - $limit, $limit)->all();
        $paginatedItems= new LengthAwarePaginator($currentPageItems , count($collection), $limit);
        $paginatedItems->setPath($request->url());

        return $paginatedItems;

    }
}
