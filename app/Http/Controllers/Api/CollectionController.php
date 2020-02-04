<?php

namespace App\Http\Controllers\Api;

use App\Models\ShopifyExcelUpload;
use App\Http\Controllers\Controller;
use App\User;
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
        $users = request('users');
        $PDC = (request('pdc') == 'yes'? true:false);

        return $this->GetJsonData($daterange,$location,$mode,$users,$PDC);
    }

    /**
     *
     * @param $daterange
     * @param $location
     * @param $mode
     * @param $users
     * @param $PDC
     * @return array
     */
    private function GetDocuments($daterange,$location,$mode,$users,$PDC)
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
            $documents->whereIn('student_school_location', explode(',',$location));
        }
        if (!empty($mode)) {
            $documents->where('payments.mode_of_payment', $mode);
        }
        if (!empty($users)){
            $user_ids = array_column(User::whereIn('email',explode(',',$users))->get(['_id'])->toArray(),'_id');
            $documents->whereIn('uploaded_by',$user_ids);
        }
        if (!empty($PDC)){
            $documents->where('payments.is_pdc_payment',$PDC);
        }
        // Fetch only required fields
        $documents = $documents->get(['_id','student_school_location','payments.amount',
            'payments.processed','payments.mode_of_payment','payments.is_pdc_payment'])->toArray();
        return [$documents,$start_month,$end_month];

    }

    /**
     * @param $daterange
     * @param $location
     * @param $mode
     * @param $users
     * @param $PDC
     * @return array
     */
    private function GetJsonData($daterange,$location,$mode,$users,$PDC)
    {
        $Collection_Data = [];
        [$documents, $start_month, $end_month] = $this->GetDocuments($daterange, $location, $mode,$users,$PDC);

        $groupedData = GroupByKey($documents,'student_school_location');

        $Collection_Data['month'] = $start_month;
        if ($start_month != $end_month) {
            $Collection_Data['month'] = $start_month . '-' . $end_month;
        }
        foreach ($groupedData as $Data) {
            $collection = $this->GetTotalAmount($Data,$mode,$PDC);
            $Collection_Data['collection'][] = $collection;
        }
        return $Collection_Data;
    }

    /**
     * Function for getting total collection amount by adding only processed payments fetched after filter and filtering
     * on the basis of payment mode and pdc payment if not empty.
     *
     * @param $Data
     * @param $mode
     * @param $PDC
     * @return array
     */
    private function GetTotalAmount($Data,$mode,$PDC)
    {
        $collection = [];
        $total_amount = 0;
        foreach ($Data as $document) {
            $processed_keys = array_keys(array_column($document['payments'], 'processed'), "Yes");
            $keys = $processed_keys;
            if(!empty($mode)){
                $mode_keys = array_keys(array_column($document['payments'], 'mode_of_payment'), $mode);
                $keys = array_intersect($processed_keys,$mode_keys);
            }
            if(!empty($PDC)){
                $pdc_keys = array_keys(array_column($document['payments'],'is_pdc_payment'),$PDC);
                $keys = (!empty($mode_keys) ? array_intersect($pdc_keys,$mode_keys) : $pdc_keys);
            }
            foreach ($keys as $index => $key) {
                $amount = $document['payments'][$key]['amount'];
                $total_amount += $amount;
            }
            $collection['location'] = $document['student_school_location'];
            $collection['amount'] = $total_amount;
        }
        return $collection;
    }
}
