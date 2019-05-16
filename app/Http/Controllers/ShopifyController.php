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

ini_set('max_execution_time', 180);

class ShopifyController extends BaseController
{
    public function upload() {
    	
		$breadcrumb = ['Shopify' => '/bulkupload/previous/orders', 'New Upload' => ''];

	    return view('shopify.orders-bulk-upload')->with('breadcrumb', $breadcrumb);
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
	        if (!is_dir($user_name)) {
	            mkdir($user_name);
	        }

	        // Extracting file from Post request
	        $excel_file = $request->file('file');

	        // Adding current timestamp to file
	        $originalFileName = $excel_file->getClientOriginalName();
	        $fileName = time() . '_' . $originalFileName;
	        $filePath = storage_path('uploads/' . $user_name);
	        $path = $excel_file->move($filePath, $fileName);

	        // Upload the file with metadata
	        $Upload = Upload::create([
	        	'user_id' => \Auth::user()->id,
		        'file_name' => $originalFileName,
		        'path' => $path->getRealPath(),
		        'status' => Upload::STATUS_PENDING,
		        'type' => Upload::TYPE_SHOPIFY_ORDERS,
		        'created_at' => time()
	        ]);

	        // Loading the excel file
	        $ExlReader = Excel::load($path->getRealPath(), function () {
	        })->get()->first();

	        // Create Excel Raw object
	        $header = $ExlReader->first()->keys()->toArray();
		    $ExcelRaw = (new \App\Library\Shopify\Excel($header, $ExlReader->toArray(), [
		        'upload_date' => $request['date'],
			    'uploaded_by' => Auth::user()->id,
			    'file_id' => uniqid('shopify_'), # Unique identifier for the documents belonging to a single file
			    'job_status' => 'pending',
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

	            if(!DB::check_shopify_activity_id_in_database($activity_id)){
	                $errors[$valid_row['sno']] = "The activity id is not present in the database";
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
	                $doc_id = $OrderRow["_id"];

	                $order_id = $OrderRow["order_id"];
	                $final_fee = $OrderRow["final_fee_incl_gst"];

	                $isUpdateInInstallment = false;
	                // If there is any installments details provided in excel
	                if (array_key_exists('installments', $OrderRow)) {
	                    $installment_data = $OrderRow["installments"];
	                    $excel_installment_data = $valid_row["installments"];

	                    foreach ($excel_installment_data as $index => $installment){
	                        if (empty($installment_data[$index])) {
			                    $installment_data[$index] = $installment;
			                    $isUpdateInInstallment = true;
		                    }
	                    }

		                $total_installment_amount = 0;
	                    foreach ($installment_data as $index => $updated_installment) {
		                    $total_installment_amount += $updated_installment['installment_amount'];
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
			                    'installments' => $installment_data,
			                    'job_status' => 'pending',
			                    '_id' => $doc_id
		                    ];
	                    }
	                }
	            }
	        }

	        $metadata['new_order'] = $metadata['update_order'] = 0;

		    $objectIDList = [];
	        if (empty($errors)) {
	            foreach ($upsertList as $document) {
					if (empty($document['_id'])) {
						$objectIDList[] = ShopifyExcelUpload::create($document)->id;
						$metadata['new_order'] += 1;
					} else {
						$_id = $document['_id'];
						unset($document['_id']);
						$metadata['update_order'] += 1;

	                    // Update installment in database
						ShopifyExcelUpload::where('id', $_id)
						                  ->update($document);

	                    // Store the object id to be used to send document in job queue
	                    $objectIDList[] = $_id;
					}
		        }

		        $Upload->status = Upload::STATUS_SUCCESS;
	            $Upload->metadata = $metadata;
	            $Upload->save();
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

	    $Uploads = Upload::where('user_id', \Auth::user()->id)->orderBy('created_at', 'desc')->get();

        return view( 'shopify.past-files-upload')->with('files', $Uploads)->with('breadcrumb', $breadcrumb);
    }

    public function previous_orders() {
        $mongodb_records = ShopifyExcelUpload::where('uploaded_by', Auth::user()->id)->get();

	    $breadcrumb = ['Shopify' => '/bulkupload/previous/orders', 'Previous orders' => ''];

	    return view('shopify.previous-orders')
		    ->with('records_array', $mongodb_records)
		    ->with('breadcrumb', $breadcrumb);
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


