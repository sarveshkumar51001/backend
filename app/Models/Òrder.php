<?php

namespace App\Models;

class Order extends Base
{
	protected $connection = 'mongodb';
	protected $collection = 'orders';
	protected $guarded = [];


	public function scopeSearchOrder($query){

		return $query->where('student name', 'like', "%$this->query%")
	                   ->orWhere('student_id', 'like', "%$this->query%")
	                   ->orWhere('class', 'like', "%$this->query%")
	                   ->orWhere('school', 'like', "%$this->query%")
	                   ->orWhere('contact_email', 'like', "%$this->query%")
	                   ->orWhere('contact_no', 'like', "%$this->query%");

	}
}