<?php

namespace App\Http\Controllers\Api;

use App\Models\ShopifyExcelUpload;
use Carbon\Carbon;
use Maatwebsite\Excel;
use App\Exports\CollectionExport;
use App\Http\Controllers\Controller;
use App\Library\Collection\Collection;

/**
 * Class CollectionController
 * @package App\Http\Controllers\Api
 */
class CollectionController extends Controller
{
    public function collection() {
    	if (!request('token') || request('token') != env('API_TOKEN')) {
    	    return response(['errors' => ['You don\'t have access.']], 403);
	    }

	    // Fetching timestamps and month for start and end date.
	    $start = request('range_from') ? Carbon::createFromFormat('d/m/Y', request('range_from')) : Carbon::now()->startOfMonth();
	    $end = request('range_to') ? Carbon::createFromFormat('d/m/Y', request('range_to')) : Carbon::now();

	    $users = request('users') ? explode(',', strtolower(request('users'))) : [];
        $locations = request('location') ? explode(',', strtolower(request('location'))) : [];

	    // Taking case insensitive mode as input and returning the mapped mode for database query
	    $payment_mode = "";
	    $mapping = ShopifyExcelUpload::PAYMENT_MODE_CASE_MAPPING;
	    if(array_key_exists(strtolower(request('mode')),$mapping)){
	        $payment_mode = $mapping[strtolower(request('mode'))];
        }

        $locationList = [];
	    if(!empty($locations)) {
            foreach ($locations as $location) {
                $locationList[] = ucwords($location);
            }
        }
	    $break_by = (request('break_by')) ? request('break_by') : '';

	    $Collection = new Collection();

        $Collection->setStart($start)
            ->setEnd($end)
            ->setMode($payment_mode)
            ->setUsers($users)
            ->setLocation($locationList)
            ->setIsPDC(strtolower(request('pdc')) == 'yes')
            ->setBreakBy($break_by);

        if(strtolower(request('format')) == 'csv') {
            return Excel\Facades\Excel::download(new CollectionExport($break_by,$Collection->Get()->toCSVFormat()), 'collection.csv');
        }
        return response()->json($Collection->Get()->toJsonFormat());
    }
}
