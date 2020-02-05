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
	public $users, $location = [];
	public $results, $groupedData = [];
	public $isPDC = true;

	public $format = 'JSON';

	private $columns = [
		'_id',
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

	/**
	 *
	 */
	private function Format() {
		$groupedData = [];
		foreach ($this->results as $result) {
			if (!isset($groupedData[$result->student_school_location])) {
				$groupedData[$result->student_school_location] = [];
			}

			$monthlyTotal = $this->GetTotalAmountBreakUp($result->toArray());
			foreach ($monthlyTotal as $month => $total) {
				if (isset($groupedData[$result->student_school_location][$month])) {
					$groupedData[$result->student_school_location][$month] += $total;
				} else {
					$groupedData[$result->student_school_location][$month] = $total;
				}
			}
		}

		$this->groupedData = $groupedData;

		/**
		 * [
		 *   "Saket" => [
		 *                  "January 2019": 20000,
		 *                  "February 2019" : 3000
		 *
		 *              ],
		 *   "Pitampura" => [
		 *                  "January 2019": 20000,
		 *                  "February 2019" : 3000
		 *
		 *              ]
		 * ]
		 *
		 */

		if ($this->format == 'CSV') {
			return $this->toCSV();
		}

		return $this->toJson();
	}

	/**
	 * @param string $format
	 *
	 * @return Collection
	 */
	public function setFormat(string $format = 'JSON') {
		$this->format = $format;

		return $this;
	}

	private function toJson() {
		$locations = array_keys($this->groupedData);

		$jsonArray = [];
		foreach (CarbonPeriod::create($this->Start, '1 month', $this->End) as $Month) {
			$month = [];
			$month['month'] = $Month->format('F Y');

			$collection = [];
			foreach ($locations as $location) {
				$collection[] = [
					'location' => $location,
					'amount'   => $this->groupedData[$location][$Month->format('F Y')] ?? 0
				];
			}

			$month['collection'] = $collection;

			$jsonArray[] = $month;
		}

		return json_encode($jsonArray);

	}

	private function toCSV() {
		$locations = array_keys($this->groupedData);

		$csvList = [];
		foreach (CarbonPeriod::create($this->Start, '1 month', $this->End) as $Month) {
			$month = [];
			foreach ($locations as $location) {
				$month['Month'] = $Month->format('F Y');
				$month['Location'] = $location;
				$month['Amount'] = $this->groupedData[$location][$Month->format('F Y')] ?? 0;

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

		// Looping through all the keys obtained to calculate the total collected/due amount.
		foreach ($final_keys as $key) {
			$amount = $document['payments'][$key]['amount'];

			$period = Carbon::createFromTimestamp($document['payments'][$key]['upload_date'])->format('F Y');
			if (isset($monthlyTotal[$period])) {
				$monthlyTotal[$period] += $amount;
			} else {
				$monthlyTotal[$period] = $amount;
			}
		}
		return $monthlyTotal;
	}
}
