<?php

namespace App\Models;

class Order extends Base
{
	protected $connection = 'mongodb';
	protected $collection = 'orders';
	protected $guarded = [];
}