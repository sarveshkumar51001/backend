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
	    return Customer::where('customer_id', 'like', "%$this->query%")
                      ->orWhere('customer_name', 'like', "%$this->query%")
                      ->orWhere('contact_no', 'like', "%$this->query%")
                      ->orWhere('contact_email', 'like', "%$this->query%")
                      ->orWhere('address', 'like', "%$this->query%")
                      ->paginate($this->limit);
    }

    private function Products() {
        return Product::orWhere('title', 'like', "%$this->query%")
                        ->orWhere('product_type', 'like', "%$this->query%")
                        ->orWhere('tags', 'like', "%$this->query%")
                        ->orWhere('variants.sku', 'like', "%$this->query%")
                        ->where('domain_store', env('SHOPIFY_STORE'))
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
