<?php

namespace App\Models;

class ShopifyExcelUpload extends Base
{
	protected $connection = 'mongodb';
	protected $collection = 'shopify_excel_uploads';
	protected $guarded = [];
}