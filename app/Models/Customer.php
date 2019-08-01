<?php

namespace App\Models;

class Customer extends Base
{
	protected $connection = 'mongodb';
	protected $collection = 'customers';
	protected $guarded = [];

	public function scopeCustomerSearch($query)
    {
        return $query->where('customer_id', 'like', "%$this->query%")
	                  ->orWhere('customer_name', 'like', "%$this->query%")
	                  ->orWhere('contact_no', 'like', "%$this->query%")
	                  ->orWhere('contact_email', 'like', "%$this->query%")
	                  ->orWhere('address', 'like', "%$this->query%");
    }
}