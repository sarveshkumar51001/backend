<?php

namespace App\Http\Controllers;

use App\Models\Product;

class ProductController extends Controller
{
    public function __construct() {
        $this->middleware('auth');
    }

    public function index() {
	    $limit = 100;
	    $data = Product::paginate($limit);

	    return view('products-list', ['products' => $data]);
    }
}
