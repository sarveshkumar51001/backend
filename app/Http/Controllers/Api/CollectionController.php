<?php

namespace App\Http\Controllers\Api;

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
	    $users = request('users') ? explode(',', request('users')) : [];
	    $locationList = request('location') ? explode(',', request('location')) : [];
	    $break_by = (request('break_by')) ? request('break_by') : '';

	    $Collection = new Collection();

        $Collection->setStart($start)
            ->setEnd($end)
            ->setMode(request('mode'))
            ->setUsers($users)
            ->setLocation($locationList)
            ->setIsPDC(request('pdc') == 'yes')
            ->setBreakBy($break_by);

        if(strtolower(request('format')) == 'csv') {
            return Excel\Facades\Excel::download(new CollectionExport($Collection->Get()->toCSVFormat()), 'collection.csv');
        }
        return response()->json($Collection->Get()->toJsonFormat());
    }
}
