<?php

namespace App\Http\Controllers\BulkUpload;

use App\Http\Controllers\BaseController;
use App\Imports\ReconcilationImport;
use App\Library\Shopify\Reconciliation\File;
use App\Library\Shopify\Reconciliation\Reconcile;
use App\Library\Shopify\Reconciliation\Source\ISource;
use App\Library\Shopify\Reconciliation\Validate;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Imports\HeadingRowFormatter;

class ReconcileController extends BaseController
{
    public function index() {
        return view('shopify.reconcile.index');
    }

    public function preview(Request $request) {
        $rules = [
            "source" => "required",
            "file" => "required|mimes:csv,txt"
        ];

        Validator::make($request->all(), $rules)->validate();

        HeadingRowFormatter::default('shopify_bulk_upload');
        $data = $request->all();

        $file = $request->file('file');
        $originalFileName = $file->getClientOriginalName();
        $file_name = time() . "_$originalFileName";
        $filePath = storage_path('uploads/reconciliation/');
        $final_path = $file->move($filePath, $file_name);

        $Rows = Arr::first(Excel::toArray(new ReconcilationImport(), $final_path->getRealPath()));
        $Headers = array_keys(Arr::first($Rows));

        $File = new File($Headers, $Rows, $final_path, $data['source']);
        $errors = (new Validate($File))->Run();
        /* @var ISource $sourceClass */
        $sourceClass = Reconcile::GetSourceClass($data['source']);
        $columns  = $sourceClass::GetColumnTitles();

        if(empty($errors)) {
            $Reconcile = Reconcile::Instance($File, Reconcile::MODE_SANDBOX);
            list($result, $metadata) = $Reconcile->Run();
        }

        // Next Step will be enable only when there is at-least one returned or settled rows founds
        $meta = [];
        if(!empty($metadata['returned_rows_count']) || !empty($metadata['total_settled_rows_count'])) {
            $meta['nextstep'] = Reconcile::MODE_SETTLE;
            $meta['checksum'] = md5(env('API_SALT') . md5_file($final_path));
            $meta['filePath'] = $final_path;
            $meta['source'] = $data['source'];
        }
        $output = ['previewdata' => $result, 'errors' => $errors, 'meta' => $meta, 'metrics' => $metadata, 'columns' => $columns];

        return view('shopify.reconcile.preview', $output);
    }
}
