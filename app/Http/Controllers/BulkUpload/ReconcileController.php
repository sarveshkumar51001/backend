<?php

namespace App\Http\Controllers\BulkUpload;

use App\Http\Controllers\BaseController;
use App\Imports\ReconcilationImport;
use App\Library\Permission;
use App\Library\Shopify\Reconciliation\File;
use App\Library\Shopify\Reconciliation\Payment;
use App\Library\Shopify\Reconciliation\Reconcile;
use App\Library\Shopify\Reconciliation\Source\ISource;
use App\Library\Shopify\Reconciliation\Validate;
use App\Models\ReconcileStatement;
use App\Models\ShopifyExcelUpload;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Imports\HeadingRowFormatter;

class ReconcileController extends BaseController
{

    public function index() {
        $breadcrumb = ['Reconcile' => ''];

        if(!has_permission(Permission::PERMISSION_RECONCILE)) {
            return abort('403');
        }
        $reco_data = [];
        $range = '';
        if (has_permission('reconcile'))
        {
            $OrderORM = ShopifyExcelUpload::orderBy('_id', 'DESC');
            if (!empty(request('daterange'))) {
                [$start_date, $end_date] = GetStartEndDate(request('daterange'));
                $end_date = 0;
                $OrderORM->where('payments.upload_date' ,'>', $start_date);
                $range = request('daterange');
            } else {
                if (\Carbon\Carbon::now()->format('n') >= 4) {
                    $year = Carbon::now()->format('Y');
                } else {
                    $year = Carbon::now()->format('Y') - 1;
                }
                $range = Carbon::parse('01-04-' . $year)->format('m/d/Y') . " - " . Carbon::parse(Carbon::now())->format('m/d/Y');
                $start_date = Carbon::parse('01-04-' . $year)->timestamp;
                $end_date = 0;
                $OrderORM->where('payments.upload_date' ,'>', $start_date);
            }

            $Orders = $OrderORM->get();
            $reco_data = [
                'all' => [
                    'amount' => 0,
                    'pdc_count' => 0,
                    'pdc_amount' => 0,
                    'count' => 0
                ],
                'pending' => [
                    'amount' => 0,
                    'count' => 0
                ],
                'settled' => [
                    'amount' => 0,
                    'count' => 0
                ],
                'returned' => [
                    'amount' => 0,
                    'count' => 0
                ],
                'total' => [
                    'amount' => 0,
                    'count' => 0
                ],
            ];

            foreach ($Orders as $Order) {
                foreach ($Order['payments'] as $payment) {
                    $isPdc = ($payment['is_pdc_payment'] == true && !empty($payment['chequedd_date'])
                        && Carbon::createFromFormat('d/m/Y', $payment['chequedd_date'])->timestamp > $end_date);
                    $Payment = new Payment($payment);

                    $amount = $Payment->getAmount();
                    if(!$isPdc) {
                        $reco_data['all']['amount'] += $amount;
                        $reco_data['all']['count'] += 1;
                        if ($Payment->isSettled()) {
                            $reco_data['settled']['amount'] += $Payment->getAmount();
                            $reco_data['settled']['count'] += 1;
                        } elseif ($Payment->isReturned()) {
                            $reco_data['returned']['amount'] += $Payment->getAmount();
                            $reco_data['returned']['count'] += 1;
                        } else {
                            $reco_data['pending']['amount'] += $Payment->getAmount();
                            $reco_data['pending']['count'] += 1;
                        }
                    }
                    if ($isPdc) {
                        $reco_data['all']['pdc_count'] += 1;
                        $reco_data['all']['pdc_amount'] += $amount;
                    }
                    $reco_data['total']['amount'] += $amount;
                    $reco_data['total']['count'] += 1;
                }
            }
        }
        $ReconcileStatement = ReconcileStatement::select('status', 'source', 'imported_at', 'imported_by', 'meta_data')
            ->where('status', 1)
            ->orderBy('imported_at', 'desc')->paginate(50);

        return view('shopify.reconcile.index')
            ->with('breadcrumb', $breadcrumb)
            ->with('history', $ReconcileStatement)
            ->with('range', $range)
            ->with('reco_data', $reco_data);
    }

    public function preview(Request $request) {

        if(!has_permission(Permission::PERMISSION_RECONCILE)) {
            return abort(403);
        }

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
        $result = $metadata = [];

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
