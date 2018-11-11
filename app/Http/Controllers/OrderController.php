<?php

namespace App\Http\Controllers;

class OrderController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        return view('orders-list');
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
