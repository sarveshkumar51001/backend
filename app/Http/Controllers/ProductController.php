<?php

namespace App\Http\Controllers;

use App\Models\Product;

class ProductController extends BaseController
{
    public function index() {
	    $limit = 100;
	    $data = Product::paginate($limit);
	    $breadcrumb = ['Products' => ''];

	    return view('products-list', ['products' => $data, 'breadcrumb' => $breadcrumb]);
    }

	public function view($id) {
    	$product = Product::where('id', intval($id))->first();
		$breadcrumb = ['Products' => url('products'), $product->title => ''];


		return view('product-view', ['product' => $product, 'breadcrumb' => $breadcrumb]);
	}
}
