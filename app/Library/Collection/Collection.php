<?php

namespace App\Library\Collection;


use App\User;
use Carbon\Carbon;
use App\Models\ShopifyExcelUpload;
use Carbon\CarbonPeriod;

/**
 * Class Collection
 */
class Collection
{
	public $Start, $End, $mode;
	public $users, $location = [],$break;
	public $results, $groupedData = [];
	public $isPDC = true;

	private $columns = [
		'_id',
        'activity',
        'branch',
		'student_school_location',
		'payments.amount',
		'payments.processed',
		'payments.mode_of_payment',
		'payments.is_pdc_payment',
		'payments.upload_date',
	];

	/**
	 * Initialised the range default to current month
	 */
	public function __construct() {
		$this->Start = Carbon::now()->startOfMonth();
		$this->End   = Carbon::now();
	}

	public function HasTxnFilter() {
		return ($this->isPDC == true || !empty($this->mode));
	}

	public function Get() {
		$this->LoadFromDB();

		$data = $this->Format();

		return $data;
	}

	/**
	 * @param mixed $Start
	 *
	 * @return Collection
	 */
	public function setStart(Carbon $Start) {
		$this->Start = $Start;

		return $this;
	}

	/**
	 * @param mixed $End
	 *
	 * @return Collection
	 */
	public function setEnd(Carbon $End) {
		$this->End = $End;

		return $this;
	}

	/**
	 * @param mixed $location
	 *
	 * @return Collection
	 */
	public function setLocation($location) {
		$this->location = $location;

		return $this;
	}

	/**
	 * @param mixed $mode
	 *
	 * @return Collection
	 */
	public function setMode($mode) {
		$this->mode = $mode;

		return $this;
	}

	/**
	 * @param array $users
	 *
	 * @return $this
	 */
	public function setUsers(array $users) {
		$this->users = $users;

		return $this;
	}

	public function setBreakBy($break) {
	    $this->break = $break;

	    return $this;
    }

	private function LoadFromDB() {
		// If daterange is empty then assign current month as start and end time for fetching documents else
		// the daterange sent in request.
		$documents = ShopifyExcelUpload::whereBetween('payments.upload_date', [$this->Start->timestamp, $this->End->timestamp]);

		// If not empty location then filter the documents on location.
		// Location can be single as well as in the form of csv.
		if (!empty($this->location)) {
			$documents->whereIn('student_school_location', $this->location);
		}

		// If not empty mode then filter the documents on mode.
		if (!empty($this->mode)) {
			$documents->where('payments.mode_of_payment', $this->mode);
		}

		// If not empty users then filter the documents on users.
		// Users can be single as well as in the form of csv.
		if (!empty($this->users)){
			$userIDList = array_column(User::whereIn('email', $this->users)->get(['_id'])->toArray(),'_id');
			$documents->whereIn('uploaded_by', $userIDList);
		}

		// If not empty PDC then filter the documents on PDC status i.e. true/false.
		if ($this->isPDC == true){
			$documents->where('payments.is_pdc_payment', true);
		}

		// Fetch only required fields
		$this->results = $documents->get($this->columns);
	}

	private function BreakByMapping(){
	    if($this->break == 'product')
	        $column = 'activity';
	    elseif($this->break == 'branch')
            $column = 'branch';
	    else
	        $column = 'student_school_location';

	    return $column;
    }

	/**
	 * @return $this
	 */
	private function Format() {
		$groupedData = [];
		$column = $this->BreakByMapping();
		foreach ($this->results as $result) {
            if (!isset($groupedData[$result->$column])) {
                $groupedData[$result->$column] = [];
            }
            $monthlyTotal = $this->GetTotalAmountBreakUp($result->toArray());
            foreach ($monthlyTotal as $month => $totalData) {
                if (isset($groupedData[$result->$column][$month])) {
                    $groupedData[$result->$column][$month]['total'] += $totalData['total'];
                    $groupedData[$result->$column][$month]['txn_count'] += $totalData['txn_count'];
                    $groupedData[$result->$column][$month]['order_count'] += $totalData['order_count'];
                } else {
                    $groupedData[$result->$column][$month]['total'] = $totalData['total'];
                    $groupedData[$result->$column][$month]['txn_count'] = $totalData['txn_count'];
                    $groupedData[$result->$column][$month]['order_count'] = $totalData['order_count'];
                }
            }
		}
		// Standard format
		/**
		 * [
		 *   "Product/Location/Branch" => [
		 *                  "January 2019":  [
		 *                           total => 20000
		 *                           txn_count => 10
         *                           order_count => 5
		 *                          ],
		 *                  "February 2019":  [
		 *                           total => 20000
		 *                           txn_count => 10
         *                           order_count => 5
		 *                          ],
		 *
		 *              ],
		 *   "Product/Location/Branch" => [
		 *                  ""January 2019":  [
		 *                           total => 20000
		 *                           txn_count => 10
         *                           order_count => 5
		 *                          ],
		 *                  "February 2019":  [
		 *                           total => 20000
		 *                           txn_count => 10
         *                           order_count => 5
		 *                          ],
		 *
		 *              ]
		 * ]
		 *
		 */
		$this->groupedData = $groupedData;
		return $this;
	}

	public function toJsonFormat() {
		$group_keys = array_keys($this->groupedData);

		$jsonArray = [];
		foreach (CarbonPeriod::create($this->Start, '1 month', $this->End) as $Month) {
			$month = [];
			$month['month'] = $Month->format('F Y');

			$collection = [];
			foreach ($group_keys as $key) {
				$collection[] = [
                    ($this->break) ? $this->break :'location' => $key,
					'amount'   => $this->groupedData[$key][$Month->format('F Y')]['total'] ?? 0,
					'txn_count' => $this->groupedData[$key][$Month->format('F Y')]['txn_count'] ?? 0,
                    'order_count' => $this->groupedData[$key][$Month->format('F Y')]['order_count'] ?? 0
				];
			}

			$month['collection'] = $collection;

			$jsonArray[] = $month;
		}

		return $jsonArray;

	}

	public function toCSVFormat() {
		$group_keys = array_keys($this->groupedData);

		$csvList = [];
		foreach (CarbonPeriod::create($this->Start, '1 month', $this->End) as $Month) {
			$month = [];
			foreach ($group_keys as $key) {
                $month['Month'] = $Month->format('F Y');
                $month[($this->break) ? $this->break :'location'] = $key;
				$month['Order Count'] = $this->groupedData[$key][$Month->format('F Y')]['order_count'] ?? 0;
				$month['Txn Count'] = $this->groupedData[$key][$Month->format('F Y')]['txn_count'] ?? 0;
				$month['Amount'] = $this->groupedData[$key][$Month->format('F Y')]['total'] ?? 0;

				$csvList[] = $month;
			}
		}
		return $csvList;
	}

	/**
	 * @param bool $isPDC
	 *
	 * @return Collection
	 */
	public function setIsPDC( bool $isPDC ) {
		$this->isPDC = $isPDC;

		return $this;
	}

	/**
	 * Getting total collection amount by adding only processed transactions also add transaction level filters
	 *
	 * @param array $document
	 *
	 * @return array
	 * [
	 *    "January 2019" => 1000
	 *    "February 2019" => 2000
	 * ]
	 */
	private function GetTotalAmountBreakUp(array $document)
	{

		$monthlyTotal = $final_keys = $mode_keys = $pdc_keys = [];
		$period = "";
		$processed_keys = array_keys(array_column($document['payments'], 'processed'), "Yes");

		// Finding matching payments i.e. payments which are processed as well as payment mode equal to $mode.
		if(!empty($this->mode)) {
			$mode_keys = array_keys(array_column($document['payments'], 'mode_of_payment'), $this->mode);
			$final_keys = array_merge($final_keys, $mode_keys);
		}

		// Finding matching payments in which PDC is true and mode is equal to $mode iff $mode is present else
		// just return payments with PDC true.
		if($this->isPDC) {
			$pdc_keys = array_keys(array_column($document['payments'],'is_pdc_payment'), $this->isPDC);
			$final_keys = array_merge($final_keys, $pdc_keys);
		}

		if (!$this->HasTxnFilter()) {
			$final_keys = $processed_keys;
		}

		if(empty($final_keys)){
		    return [];
        }
		// Looping through all the keys obtained to calculate the total collected/due amount.
		foreach ($final_keys as $key) {
			$amount = $document['payments'][$key]['amount'];
			$period = Carbon::createFromTimestamp($document['payments'][$key]['upload_date'])->format('F Y');
			if (isset($monthlyTotal[$period])) {
				$monthlyTotal[$period]['total'] += $amount;
				$monthlyTotal[$period]['txn_count'] += 1;
			} else {
				$monthlyTotal[$period]['total'] = $amount;
				$monthlyTotal[$period]['txn_count'] = 1;
				$monthlyTotal[$period]['order_count'] = 0;
			}
		}
		$monthlyTotal[$period]['order_count'] += 1;
		return $monthlyTotal;
	}
}
