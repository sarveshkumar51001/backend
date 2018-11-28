<?php

namespace App\Http\Controllers;

use App\Models\Customer;

class CustomerController extends BaseController
{
    public function index() {
	    $limit = 100;
	    $data = Customer::paginate($limit);
	    $breadcrumb = ['Customers' => ''];
	    return view('customers-list', ['users' => $data, 'breadcrumb' => $breadcrumb]);
    }

	public function view($id) {
		$customer = Customer::find($id);
		$customerDetails = \DB::collection('customer_details')->where('student_id', '=', $customer->student_id)->first();

		$breadcrumb = ['Customers' => url('customers'), $customer->student_name => ''];

		return view('customer-view', ['customer' => $customer, 'customer_details' => $customerDetails, 'breadcrumb' => $breadcrumb]);
	}

    public function profiler() {
	    $data = \DB::collection('customer_profiler_data')->get();
	    $breadcrumb = ['Customers profiler result' => ''];

	    return view('profiler-list', ['profiles' => $data, 'breadcrumb' => $breadcrumb]);
    }
}
