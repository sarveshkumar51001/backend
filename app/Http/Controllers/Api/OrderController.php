<?php

namespace App\Http\Controllers\Api;

use App\Models\ShopifyExcelUpload;
use App\Http\Controllers\Controller;

/**
 * Class OrderController
 * @package App\Http\Controllers\Api
 */
class OrderController extends Controller
{
	public function get_upload_details($uploadID) {
		return ShopifyExcelUpload::find($uploadID)->only('payments');
	}
}
