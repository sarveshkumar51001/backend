<?php
namespace App\Http\Controllers;

use App\Models\ShopifyExcelUpload;
use App\Models\Upload;
use Auth, Exception;
use Illuminate\Http\Request;
use App\Jobs\ShopifyOrderCreation;
use Maatwebsite\Excel\Facades\Excel;
use App\Library\Shopify\ExcelValidator;
use MongoDB\Driver\Exception\BulkWriteException;
use App\Library\Shopify\DB;
use App\Library\Shopify\API;
use Illuminate\Support\Facades\Validator;


ini_set('max_execution_time', 180);

class ShopifyController extends BaseController
{
	private static $adminTeam = [
		'zuhaib@valedra.com', 'ishaan.jain@valedra.com', 'bishwanath@valedra.com', 'kartik@valedra.com'
	];

    public function upload() {
	    $breadcrumb = ['Shopify' => '/bulkupload/previous/orders', 'New Upload' => ''];

	    return view('shopify.orders-bulk-upload')
		    ->with('breadcrumb', $breadcrumb);
    }

	/**
	 * @param Request $request
	 *
	 * @return $this
	 * @throws Exception
	 */
    public function upload_preview(Request $request)
    {
    	if (!$request->isMethod('post')){
    		return redirect('/bulkupload/');
	    }

	    $validator = Validator::make($request->all(),['file' => 'mimes:xls']);

	    if ($validator->fails()) { 
            return view('shopify.orders-bulk-upload')->withErrors($validator);
        }

	    $breadcrumb = ['Shopify' => '/bulkupload/previous/orders', 'Upload Preview' => ''];

	    # Configuring Laravel Excel for skipping header row and modifying the duplicate header names
        try {
	        config([
	            'excel.import.startRow' => 2,
		        'excel.import.heading' => 'slugged_with_count',
		        'excel.import.dates.enabled' => false
	        ]);

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

	        // Loading the excel file
	        $ExlReader = Excel::load($path->getRealPath(), function () {
	        })->get()->first();

	        // Create Excel Raw object
	        $header = $ExlReader->first()->keys()->toArray();
		    $ExcelRaw = (new \App\Library\Shopify\Excel($header, $ExlReader->toArray(), [
		        'upload_date' => $request['date'],
			    'uploaded_by' => Auth::user()->id,
			    'file_id' => uniqid('shopify_'), # Unique identifier for the documents belonging to a single file
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

		    // If any error, Return from here only
	        if ($errors) {
	        	return view('bulkupload-preview')
			        ->with('errored_data', $errors)
			        ->with('excel_response', $formattedData)
			        ->with('breadcrumb', $breadcrumb)
			        ->with('headers', $ExcelRaw->GetFormattedHeader());
	        }

	        // EVERYTHING LOOKS GOOD TO GO.......
		    # Inserting data to MongoDB after validation
	        $upsertList = [];
		    foreach ($formattedData as $valid_row) {
	            // Get the primary combination to lookup in database
	            $date_enroll = $valid_row['date_of_enrollment'];
	            $activity_id = $valid_row['shopify_activity_id'];
	            $std_enroll_no = $valid_row['school_enrollment_no'];

	            $Product = DB::get_shopify_product_from_database($activity_id);
	            if(!$Product){
	                $errors[$valid_row['sno']] = "The activity id is not present in the database";
	            } else if (empty($valid_row['activity'])) {
	            	$valid_row['activity'] = $Product['title'];
	            }

	            // Attempt to lookup in database with the key combination
		        // Ex: 06/05/2019, VAL-12345-002, SS-1112
	            $OrderRow = ShopifyExcelUpload::where('date_of_enrollment', $date_enroll)
	                           ->where('shopify_activity_id', $activity_id)
	                           ->where('school_enrollment_no', $std_enroll_no)
	                           ->first();



	            if (empty($OrderRow)) {
		            $upsertList[] = $valid_row;
	            } else {
	                $doc_id = $OrderRow->_id;
	                $order_id = $OrderRow->order_id;
	                $final_fee = $OrderRow->final_fee_incl_gst;

	                $isUpdateInInstallment = false;
		            $existingPaymentData = $OrderRow->payments;
		            $existingPaymentInstallments = array_column($existingPaymentData, 'installment');

		            // If there is any installments details provided in excel
                    foreach ($valid_row["payments"] as $payment) {
	                    /**
	                     * Consider the payment data only if it is not uploaded before
	                     * Any update in already stored installments will be ignored
	                     */
                    	if (!in_array($payment['installment'], $existingPaymentInstallments)) {
		                    $existingPaymentData[$payment['installment']] = $payment;
		                    $isUpdateInInstallment = true;
	                    }
                    }

	                $total_installment_amount = 0;
                    foreach ($existingPaymentData as $updatedPayment) {
	                    $total_installment_amount += $updatedPayment['amount'];
                    }

                    if ($total_installment_amount > $final_fee) {
                        $exception_msg = sprintf("Fee collected for the Order ID %u exceeded the order value.", $order_id);
	                    $errors[$valid_row['sno']] = $exception_msg;
                    }

                    if (!$isUpdateInInstallment) {
	                    $errors[$valid_row['sno']] = "Either same excel uploaded again or existing installments can't be modified.";
                    }

	                // If there is no error so far then only we proceed for updates
	                if (empty($errors[$valid_row['sno']])) {
		                $upsertList[] = [
		                    'payments' => $existingPaymentData,
		                    'job_status' => ShopifyExcelUpload::JOB_STATUS_PENDING,
		                    '_id' => $doc_id
	                    ];
                    }
	            }
	        }

	        $metadata['new_order'] = $metadata['update_order'] = 0;

		    $objectIDList = [];
	        if (empty($errors)) {
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
			        'user_id' => \Auth::user()->id,
			        'file_name' => $originalFileName,
			        'path' => $path->getRealPath(),
			        'metadata' => $metadata,
			        'status' => Upload::STATUS_SUCCESS,
			        'type' => Upload::TYPE_SHOPIFY_ORDERS,
			        'created_at' => time()
		        ]);
	        }

	        if (!empty($objectIDList)) {
		        // Finally dispatch the data into queue for processing
		        foreach (ShopifyExcelUpload::findMany($objectIDList) as $Object) {
			        ShopifyOrderCreation::dispatch($Object);
		        }
	        }

		    return view('bulkupload-preview')
			    ->with('errored_data', $errors)
			    ->with('excel_response', $formattedData)
			    ->with('breadcrumb', $breadcrumb)
			    ->with('headers', $ExcelRaw->GetFormattedHeader());
        } catch (BulkWriteException $bulk) {
            return view('uploaderror');
        }
    }

    public function previous_uploads() {
	    $breadcrumb = ['Shopify' => '/bulkupload/previous/orders', 'Previous uploads' => ''];

	    $Uploads = Upload::where('user_id', \Auth::user()->id)->where('status', 'success')->orderBy('created_at', 'desc')->get();

        return view( 'shopify.past-files-upload')->with('files', $Uploads)->with('breadcrumb', $breadcrumb);
    }

    public function previous_orders() {
	    $start = start_of_the_day(date('m/d/Y'));
	    $end = end_of_day(date('m/d/Y'));
	    if (request('daterange')) {
		    $range = explode(' - ', request('daterange'), 2);
		    if (count($range) == 2) {
			    $start = start_of_the_day($range[0]);
			    $end = end_of_day($range[1]);
		    }
	    }

	    if ($start && $end) {
		    if (request('filter') == 'team' && in_array(\Auth::user()->email, self::$adminTeam)) {
			    $mongodb_records = ShopifyExcelUpload::whereBetween('payments.upload_date', [$start, $end])
			                                         ->get();
		    } else {
			    $mongodb_records = ShopifyExcelUpload::where('uploaded_by', Auth::user()->id)
			                                         ->whereBetween('payments.upload_date', [$start, $end])
			                                         ->get();
		    }
	    } else {
		    $mongodb_records = ShopifyExcelUpload::where('uploaded_by', Auth::user()->id)->get();
	    }

	    $modeWiseData = [];
	    foreach (ShopifyExcelUpload::$modesTitle as $mode => $title) {
	        $modeWiseData[$mode]['count'] = $modeWiseData[$mode]['total'] = 0;
        }

	    foreach ($mongodb_records as $document) {
		    foreach ($document['payments'] as $payment) {
		    	if (!empty($payment['upload_date']) && $payment['upload_date'] >= $start && $payment['upload_date'] <= $end) {
				    $mode = strtolower($payment['mode_of_payment']);
				    if (!empty($payment['chequedd_date']) && strtotime($payment['chequedd_date']) > time()) {
					    $modeWiseData[ShopifyExcelUpload::MODE_PDC]['total'] += $payment['amount'];
					    $modeWiseData[ShopifyExcelUpload::MODE_PDC]['count'] += 1;
				    } else if($mode == 'cash') {
					    $modeWiseData[ShopifyExcelUpload::MODE_CASH]['total'] += $payment['amount'];
					    $modeWiseData[ShopifyExcelUpload::MODE_CASH]['count'] += 1;
				    } else if($mode == 'cheque') {
					    $modeWiseData[ShopifyExcelUpload::MODE_CHEQUE]['total'] += $payment['amount'];
					    $modeWiseData[ShopifyExcelUpload::MODE_CHEQUE]['count'] += 1;
				    } else if($mode == 'dd') {
					    $modeWiseData[ShopifyExcelUpload::MODE_DD]['total'] += $payment['amount'];
					    $modeWiseData[ShopifyExcelUpload::MODE_DD]['count'] += 1;
				    } else if($mode == 'online') {
					    $modeWiseData[ShopifyExcelUpload::MODE_ONLINE]['total'] += $payment['amount'];
					    $modeWiseData[ShopifyExcelUpload::MODE_ONLINE]['count'] += 1;
				    }
			    }
		    }
	    }

	    $breadcrumb = ['Shopify' => '/bulkupload/previous/orders', 'Previous orders' => ''];


	    return view('shopify.previous-orders')
		    ->with('records_array', $mongodb_records)
		    ->with('breadcrumb', $breadcrumb)
		    ->with('metadata', $modeWiseData);
    }

    public function download_previous($id) {
	    $Uploads = Upload::find($id);

	    $breadcrumb = ['Shopify' => '/bulkupload/previous/orders', 'Download' => ''];
	    if ($Uploads['user_id'] == \Auth::user()->id) {
		    return response()->download($Uploads['path']);
	    }

	    return view('admin.404')->with('breadcrumb', $breadcrumb);
    }
}


