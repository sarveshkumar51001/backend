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
    /**
     * This function returns the MongoDB document.
     *
     * Takes document id as input and query the database to fetch the document by id.
     * @param $uploadID
     * @return mixed
     */
	public function get_upload_details($uploadID) {
		return ShopifyExcelUpload::find($uploadID)->only('payments');
	}
}
