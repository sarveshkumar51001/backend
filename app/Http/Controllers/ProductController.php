<?php

namespace App\Http\Controllers;

use App\Models\Product;

class ProductController extends BaseController
{
    public function index() {
	    $limit = 100;
	    $data = Product::where('domain_store',env('SHOPIFY_STORE'))->where('published_at','!=',null)->where('variants.inventory_quantity','>',0)->paginate($limit);
	    $breadcrumb = ['Products' => ''];

	    return view('products-list', ['products' => $data, 'breadcrumb' => $breadcrumb]);
    }

	public function view($id) {
    	$product = Product::where('id', intval($id))->first();
		$breadcrumb = ['Products' => url('products'), $product->title => ''];


		return view('product-view', ['product' => $product, 'breadcrumb' => $breadcrumb]);
	}
}
