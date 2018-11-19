<?php

namespace App\Models;

class Customer extends Base
{
	protected $connection = 'mongodb';
	protected $collection = 'customers';
}