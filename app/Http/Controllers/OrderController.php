<?php

namespace App\Http\Controllers;

use App\Models\Order;

class OrderController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

	public function index() {
		$limit = 100;
		$data = Order::paginate($limit);

		return view('orders-list', ['orders' => $data]);
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
