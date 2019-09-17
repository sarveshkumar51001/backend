<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ShopifyExcelUpload;

class ProductController extends BaseController
{
    public function index() {
	    $data = Product::ActiveProduct()->orderby('updated_at','desc')->paginate(ShopifyExcelUpload::PAGINATE_LIMIT);
	    $breadcrumb = ['Products' => ''];

	    return view('products-list', ['products' => $data, 'breadcrumb' => $breadcrumb]);
    }

	public function view($id) {
    	$product = Product::where('id', intval($id))->first();
		$breadcrumb = ['Products' => url('products'), $product->title => ''];


		return view('product-view', ['product' => $product, 'breadcrumb' => $breadcrumb]);
	}
}
