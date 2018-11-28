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
    	$product = Product::find($id);
		$breadcrumb = ['Products' => url('products'), $product->product_name => ''];

		return view('product-view', ['product' => $product, 'breadcrumb' => $breadcrumb]);
	}
}
