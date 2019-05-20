<?php

namespace App\Models;

class ShopifyExcelUpload extends Base
{
	protected $connection = 'mongodb';
	protected $collection = 'shopify_excel_uploads';
	protected $guarded = [];

	const TYPE_INSTALLMENT = 'installment';
	const TYPE_ONETIME = 'one_time';
}