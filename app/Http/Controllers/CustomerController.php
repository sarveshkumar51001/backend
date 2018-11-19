<?php

namespace App\Http\Controllers;

use App\Models\Customer;

class CustomerController extends Controller
{
    public function __construct() {
        $this->middleware('auth');
    }

    public function index() {
	    $limit = 100;
	    $data = Customer::paginate($limit);
        return view('customers-list', ['users' => $data]);
    }
}
