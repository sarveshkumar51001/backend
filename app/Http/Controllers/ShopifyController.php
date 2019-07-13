<?php
namespace App\Http\Controllers;

use App\Models\ShopifyExcelUpload;
use App\Models\Upload;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Jobs\ShopifyOrderCreation;
use Maatwebsite\Excel\Facades\Excel;
use App\Library\Shopify\ExcelValidator;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class ShopifyController extends BaseController
{
	public static $adminTeam = [
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
	 * @throws \Exception
	 */
    public function upload_preview(Request $request)
    {

	    Validator::make($request->all(),['file' => 'mimes:xls'], ['mimes' => 'The format for the uploaded file should be .:values.'])->validate();

	    $breadcrumb = ['Shopify' => './previous/orders', 'Upload Preview' => ''];

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
	        $file_id = 'shopify-'.crc32(uniqid()); # Unique identifier for the documents belonging to a single file

	        // Loading the excel file
	        $ExlReader = Excel::load($path->getRealPath(), function () {
	        })->get()->first();

	        // Create Excel Raw object
	        $header = $ExlReader->first()->keys()->toArray();
		    $ExcelRaw = (new \App\Library\Shopify\Excel($header, $ExlReader->toArray(), [
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
                        }
                        
                        // Reducing the payments array if there is any reduction in number of payments
                        $diff_element = array_diff_key($existingPaymentData,$valid_row["payments"]);
                        foreach($diff_element as $key => $value){
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
    			        ShopifyOrderCreation::dispatch($Object)->delay(now()->addSeconds(10));
    		        }
    	        }
	        }

		    return view('shopify.bulkupload-preview')
			    ->with('errored_data', $errors)
			    ->with('excel_response', $formattedData)
			    ->with('breadcrumb', $breadcrumb)
			    ->with('headers', $ExcelRaw->GetFormattedHeader());
        } catch (\Exception $bulk) {
            return view('shopify.uploaderror');
        }
    }

    public function previous_uploads() {
	    $breadcrumb = ['Shopify' => '/bulkupload/previous/orders', 'Previous uploads' => ''];

	    $Uploads = Upload::where('user_id', Auth::user()->id)->where('status', 'success')->orderBy('created_at', 'desc')->get();

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
		    if (request('filter') == 'team' && in_array(Auth::user()->email, self::$adminTeam)) {
			    $mongodb_records = ShopifyExcelUpload::whereBetween('payments.upload_date', [$start, $end])->get();
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

        $successful_records = $mongodb_records->where('job_status','!=','failed');

	    foreach ($successful_records as $document) {
		    foreach ($document['payments'] as $payment) {
		    	if (!empty($payment['upload_date']) && $payment['upload_date'] >= $start && $payment['upload_date'] <= $end) {
				    $mode = strtolower($payment['mode_of_payment']);
				    if(!empty($mode)){
				    if(!empty($payment['chequedd_date']) && Carbon::createFromFormat(ShopifyExcelUpload::DATE_FORMAT,$payment['chequedd_date'])->timestamp > time()) {
					    $modeWiseData[ShopifyExcelUpload::MODE_PDC]['total'] += $payment['amount'];
					    $modeWiseData[ShopifyExcelUpload::MODE_PDC]['count'] += 1;
				    }else if($mode == strtolower(ShopifyExcelUpload::$modesTitle[ShopifyExcelUpload::MODE_CASH])) {
					    $modeWiseData[ShopifyExcelUpload::MODE_CASH]['total'] += $payment['amount'];
					    $modeWiseData[ShopifyExcelUpload::MODE_CASH]['count'] += 1;
				    }else if($mode == strtolower(ShopifyExcelUpload::$modesTitle[ShopifyExcelUpload::MODE_CHEQUE])) {
					    $modeWiseData[ShopifyExcelUpload::MODE_CHEQUE]['total'] += $payment['amount'];
					    $modeWiseData[ShopifyExcelUpload::MODE_CHEQUE]['count'] += 1;
				    }else if($mode == strtolower(ShopifyExcelUpload::$modesTitle[ShopifyExcelUpload::MODE_DD])) {
					    $modeWiseData[ShopifyExcelUpload::MODE_DD]['total'] += $payment['amount'];
					    $modeWiseData[ShopifyExcelUpload::MODE_DD]['count'] += 1;
				    }else if($mode == strtolower(ShopifyExcelUpload::$modesTitle[ShopifyExcelUpload::MODE_ONLINE])) {
					    $modeWiseData[ShopifyExcelUpload::MODE_ONLINE]['total'] += $payment['amount'];
					    $modeWiseData[ShopifyExcelUpload::MODE_ONLINE]['count'] += 1;
				    }else if($mode == strtolower(ShopifyExcelUpload::$modesTitle[ShopifyExcelUpload::MODE_PAYTM])) {
					    $modeWiseData[ShopifyExcelUpload::MODE_PAYTM]['total'] += $payment['amount'];
					    $modeWiseData[ShopifyExcelUpload::MODE_PAYTM]['count'] += 1;
					}else if($mode == strtolower(ShopifyExcelUpload::$modesTitle[ShopifyExcelUpload::MODE_NEFT])) {
					    $modeWiseData[ShopifyExcelUpload::MODE_NEFT]['total'] += $payment['amount'];
					    $modeWiseData[ShopifyExcelUpload::MODE_NEFT]['count'] += 1;
						}
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
	    if ($Uploads['user_id'] == Auth::user()->id) {
		    return response()->download($Uploads['path']);
	    }

	    return view('admin.404')->with('breadcrumb', $breadcrumb);
    }
}


