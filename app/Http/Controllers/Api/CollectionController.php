<?php

namespace App\Http\Controllers\Api;

use App\Models\ShopifyExcelUpload;
use App\Http\Controllers\Controller;
use Carbon\Carbon;

/**
 * Class CollectionController
 * @package App\Http\Controllers\Api
 */
class CollectionController extends Controller
{

    public function collection(){

        return $this->GetCollection();

    }

    /**
     * @return mixed
     */
    private function GetCollection()
    {
        $daterange = [request('range_from'),request('range_to')];
        $location = request('location');
        $mode = request('mode');

        return $this->GroupByData($daterange,$location,$mode);
    }

    /**
     *
     * @param $daterange
     * @param $location
     * @param $mode
     * @return array
     */
    private function GetDocuments($daterange,$location,$mode)
    {
        // If daterange is empty then assign current month as start and end time for fetching documents else
        // the daterange sent in request.
        if (empty($daterange[0]) || empty($daterange[1])) {
            $time_data = explode(',', Carbon::now()->format('F,Y'));
            $start_date = start_of_the_day(date('m/d/Y', strtotime(sprintf('first day of %s %s',$time_data[0], $time_data[1]))));
            $end_date = start_of_the_day(date('m/d/Y', strtotime(sprintf('last day of %s %s',$time_data[0],$time_data[1]))));
            $start_month = $end_month = $time_data[0];
        } else {
            $start = Carbon::createFromFormat('d/m/Y', $daterange[0]);
            $end = Carbon::createFromFormat('d/m/Y', $daterange[1]);
            $start_date = $start->timestamp;
            $end_date = $end->timestamp;
            $start_month = $start->monthName;
            $end_month = $end->monthName;
        }
        $documents = ShopifyExcelUpload::whereBetween('payments.upload_date', [$start_date, $end_date]);

        // If not empty location then filter the documents on location.
        if (!empty($location)) {
            $documents->where('student_school_location', $location);
        }

//        if (!empty($mode)) {
//            $documents->where('payments.mode_of_payment', $mode);
//        }

        // Fetch only required fields
        $documents = $documents->get(['student_school_location', 'payments.amount', 'payments.processed'])->toArray();

        return [$documents,$start_month,$end_month];

    }

    /**
     * @param $daterange
     * @param $location
     * @param $mode
     * @return array
     */
    private function GroupByData($daterange,$location,$mode)
    {
        $collection = [];
        $Collection_Data = [];
        [$documents, $start_month, $end_month] = $this->GetDocuments($daterange, $location, $mode);

        $groupedData = GroupByKey($documents,'student_school_location');

        $Collection_Data['month'] = $start_month;
        if ($start_month != $end_month) {
            $Collection_Data['month'] = $start_month . '-' . $end_month;
        }
        foreach ($groupedData as $Data) {
            $total_amount = 0;
            foreach ($Data as $document) {
                $processed_keys = array_keys(array_column($document['payments'], 'processed'), "Yes");
                foreach ($processed_keys as $index => $key) {
                    $amount = $document['payments'][$key]['amount'];
                    $total_amount += $amount;
                }
                $collection['location'] = $document['student_school_location'];
                $collection['amount'] = $total_amount;
            }
            $Collection_Data['collection'][] = $collection;
        }
        return $Collection_Data;
    }
}
