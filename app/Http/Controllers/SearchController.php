<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;

/**
 * Class SearchController
 */
class SearchController extends BaseController
{
	protected $query;
	protected $limit;

	public function __construct() {
		parent::__construct();

		$this->query = request('q');
		$this->limit = 250;
	}

	/**
     * Search result render
     * @return view
     * @throws \Exception
     */
    public function index() {
        $result['customers'] = $this->Customers();
        $result['products'] = $this->Products();
        $result['orders'] = $this->Orders();

        return view('search', ['query' => $this->query, 'result' => $result]);
    }

    private function Customers() {
	    return Customer::where('student_id', 'like', "%$this->query%")
	                  ->orWhere('student_name', 'like', "%$this->query%")
	                  ->orWhere('contact_no', 'like', "%$this->query%")
	                  ->orWhere('contact_email', 'like', "%$this->query%")
	                  ->orWhere('address', 'like', "%$this->query%")
	                  ->paginate($this->limit);
    }

    private function Products() {
	    return Product::where('product_id', 'like', "%$this->query%")
	                   ->orWhere('product_name', 'like', "%$this->query%")
	                   ->orWhere('product_category', 'like', "%$this->query%")
	                   ->orWhere('product_tags', 'like', "%$this->query%")
	                   ->orWhere('product_description', 'like', "%$this->query%")
	                   ->paginate($this->limit);
    }

    private function Orders() {
	    return Order::where('student name', 'like', "%$this->query%")
	                   ->orWhere('student_id', 'like', "%$this->query%")
	                   ->orWhere('class', 'like', "%$this->query%")
	                   ->orWhere('school', 'like', "%$this->query%")
	                   ->orWhere('contact_email', 'like', "%$this->query%")
	                   ->orWhere('contact_no', 'like', "%$this->query%")
	                   ->paginate($this->limit);
    }
}