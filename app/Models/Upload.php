<?php

namespace App\Models;

class Upload extends Base
{
	protected $connection = 'mongodb';
	protected $collection = 'file_uploads';
	protected $guarded = [];

	const TYPE_SHOPIFY_ORDERS = 'shopify_orders';

	const STATUS_PENDING = 'pending';
	const STATUS_SUCCESS = 'success';
}