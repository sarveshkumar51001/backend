<?php

namespace App\Http\Controllers;

use App\Models\Customer;

class CustomerController extends BaseController
{
    public function index() {
	    $limit = 100;
	    $data = Customer::paginate($limit);
        return view('customers-list', ['users' => $data]);
    }

    public function profiler() {
	    $data = \DB::collection('customer_profiler_data')->get();

	    return view('profiler-list', ['profiles' => $data]);
    }
}
