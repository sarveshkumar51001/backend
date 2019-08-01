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
	    return Customer::CustomerSearch()->paginate($this->limit);
    }

    private function Products() {
	    return Product::ActiveProduct()->SearchProduct()->paginate($this->limit);
    }

    private function Orders() {
	    return Order::SearchOrder()->paginate($this->limit);
    }
}