<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Imports\ReconcilationImport;
use App\Library\Shopify\Reconciliation\File;
use App\Library\Shopify\Reconciliation\Reconcile;
use App\Library\Shopify\Reconciliation\Validate;
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




    }
}
