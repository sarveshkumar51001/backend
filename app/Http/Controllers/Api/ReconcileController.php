<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Imports\ReconcilationImport;
use App\Library\Shopify\Reconciliation\File;
use App\Library\Shopify\Reconciliation\Offline;
use App\Library\Shopify\Reconciliation\Payment;
use App\Library\Shopify\Reconciliation\Reconcile;
use App\Library\Shopify\Reconciliation\Validate;
use App\Models\ShopifyExcelUpload;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;

class ReconcileController extends Controller {

    public function reconcile(Request $request) {

        // Run the validation
        $rules = [
            'source' => 'required',
            'file_path' => 'required',
            'file_checksum' => 'required'
        ];

        Validator::make($request->all(), $rules)->validate();

        // All good...
        $errors   = [];
        $data     = $request->all();
        $filePath = urldecode($data['file_path']);

        $formCheckSum = md5(env('API_SALT') . md5_file($filePath));
        if($formCheckSum != $data['file_checksum']) {
            $errors[] = sprintf("Possible CSRF attempt");
        }

        // Make sure from and to are passed
        if(empty($data['source']) || !isset(File::$source[$data['source']])) {
            $errors[] = 'Source field should be passed as a mandatory parameters';
        }

        $output = [];
        if(empty($errors)) {
            $Rows = Arr::first(Excel::toArray(new ReconcilationImport(), $filePath));
            $Headers = array_keys(Arr::first($Rows));

            $File   = new File($Headers, $Rows, $filePath, $data['source']);
            $errors = (new Validate($File))->Run();

            if(empty($errors)) {
                try {
                    // Start transaction
                    $session = DB::getMongoClient()->startSession();
                    $session->startTransaction();

                    // Trigger reconcile...
                    $Reconcile = Reconcile::Instance($File, Reconcile::MODE_SETTLE);
                    $Reconcile->Run();

                    $output = $Reconcile->GetMetadata();

                    // Commit transaction
                    $session->commitTransaction();


                } catch (\Exception $e) {
                    // Rollback in case of any exception
                    $session->abortTransaction();
                    $errors[] = $e->getMessage();
                }
            }
        }

        if(!empty($errors)) {
            return response(['errors' => $errors], 422);
        }

        return response($output, 200);
    }

    public function manual_settle(Request $request)
    {
        $updates = [];
        $transaction_ids = request('transaction_ids');

        // Looping through all the transaction ids and exploding
        foreach($transaction_ids as $ids) {

            if(empty($ids)){
                return response(['Invalid ID'], 422);
            }

            $composite_id = explode('.', $ids);
            $object_id = $composite_id[0];
            $payment_index = $composite_id[1];

            $Order = ShopifyExcelUpload::where('_id', $object_id);

            //Throwing error if the user tries to alter already settled payment.
            $Payment = new Payment(head($Order->first()->toArray()) ,$payment_index);
            if($Payment->getRecoStatus() == ShopifyExcelUpload::PAYMENT_SETTLEMENT_STATUS_SETTLED){
                return response('Already marked transaction cannot be altered.',403);
            }

            $loggedInUser = (\Auth::user()->id ?? 0);

            $updates = [
                ShopifyExcelUpload::PaymentSettlementStatus => ShopifyExcelUpload::PAYMENT_SETTLEMENT_STATUS_SETTLED,
                ShopifyExcelUpload::PaymentSettlementMode => ShopifyExcelUpload::PAYMENT_SETTLEMENT_MODE_MANUAL,
                ShopifyExcelUpload::PaymentLiquidationDate => time(),
                ShopifyExcelUpload::PaymentSettledDate => time(),
                ShopifyExcelUpload::PaymentSettledBy => $loggedInUser,
                ShopifyExcelUpload::PaymentUpdatedAt => time(),
            ];

            $column_updates = [];
            foreach ($updates as $column => $value) {
                $key_name = sprintf("payments.%s.%s.%s", $payment_index, Payment::RECO, $column);
                $column_updates[$key_name] = $value;
            }
            $Order->update($column_updates);
        }
        return response(['ok'], 200);
    }
}
