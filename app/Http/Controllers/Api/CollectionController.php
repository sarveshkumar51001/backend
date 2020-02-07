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
	    // Fetching timestamps and month for start and end date.
	    $start = request('range_from') ? Carbon::createFromFormat('d/m/Y', request('range_from')) : Carbon::now()->startOfMonth();
	    $end = request('range_to') ? Carbon::createFromFormat('d/m/Y', request('range_to')) : Carbon::now();
	    $users = request('users') ? explode(',', request('users')) : [];
	    $locationList = request('location') ? explode(',', request('location')) : [];

	    $Collection = new Collection();

        $Collection->setStart($start)
            ->setEnd($end)
            ->setMode(request('mode'))
            ->setUsers($users)
            ->setLocation($locationList)
            ->setIsPDC(request('pdc') == 'yes');

        if(request('format') == 'csv') {
            $Collection->setFormat('CSV');
            return Excel\Facades\Excel::download(new CollectionExport($Collection->Get()),'collection.csv');
        }

        return $Collection->Get();

    }
}
