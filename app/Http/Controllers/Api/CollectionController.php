<?php

namespace App\Http\Controllers\Api;

use Carbon\Carbon;
use App\Library\Collection\Collection;
use App\Http\Controllers\Controller;

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
        return $Collection->setStart($start)
            ->setEnd($end)
            ->setMode(request('mode'))
            ->setUsers($users)
            ->setLocation($locationList)
            ->setIsPDC(request('pdc') == 'yes')
            ->Get();
    }
}
