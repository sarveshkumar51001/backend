<?php

namespace App\Models;

class Product extends Base
{
	protected $connection = 'mongodb';
	protected $collection = 'products';
}