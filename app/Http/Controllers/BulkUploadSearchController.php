<?php

namespace App\Http\Controllers;

use App\Models\ShopifyExcelUpload;
use App\Models\Student;
use Illuminate\Contracts\View\Factory;
use Illuminate\View\View;

class BulkUploadSearchController extends BaseController
{
    protected $query;
    protected $limit;

    /**
     * BulkUploadSearchController constructor.
     */
    public function __construct() {
        parent::__construct();

        $this->query = request('qry');
        $this->limit = 250;
    }

    /**
     * @return Factory|View
     */
    public function index()
    {
        $result = [];
        $breadcrumb = ['Search' => ''];

            $result['students'] = $this->Students(request('school-name'));
            $result['orders'] = $this->Orders(request('date'),request('mode'));

        return view('shopify.bulkupload-search', ['breadcrumb' => $breadcrumb,'result' =>$result, 'query'=>$this->query]);
    }

    private function Students($school_name) {

        $Students = Student::where('student_first_name', 'like', "%$this->query%")
            ->orWhere('parent_first_name', 'like', "%$this->query%")
            ->orWhere('school_enrollment_no', 'like', "%$this->query%");

        if(!empty($school_name)){
            $Students->where('school_name',$school_name);
        }
            return $Students->paginate($this->limit);
    }

    private function Orders($date,$mode) {

        $Orders = ShopifyExcelUpload::where('student_first_name', 'like', "%$this->query%")
            ->orWhere('parent_first_name','like', "%$this->query%")
            ->orWhere('school_enrollment_no', 'like', "%$this->query%")
            ->orWhere('payments.drawee_account_number', 'like', "%$this->query%")
            ->orWhere('shopify_order_name','like',"%$this->query%")
            ->orWhere('payments.txn_reference_number_only_in_case_of_paytm_or_online','like',"%$this->query%");

        if(!empty($school_name)){
            $Orders->where('school_name',$school_name);
        }

        if(!empty($date)){
            $Orders->Where('date_of_enrollment',$date)
                    ->orWhere('upload_date',$date)
                    ->orwhere('payments.chequedd_date',$date);
        }

        if(!empty($mode)){
            $Orders->where('payments.mode_of_payment',$mode);
        }
        return $Orders->paginate($this->limit);

    }
















































}

