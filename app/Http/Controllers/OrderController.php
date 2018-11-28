<?php

namespace App\Http\Controllers;

use App\Models\Order;

class OrderController extends BaseController
{
	public function index() {
		$limit = 100;
		$data = Order::paginate($limit);
		$breadcrumb = ['Orders' => ''];

		return view('orders-list', ['orders' => $data, 'breadcrumb' => $breadcrumb]);
	}

	public function create()
	{
		return view('orders-create');
	}

	public function edit()
	{
		return view('home');
	}
}
