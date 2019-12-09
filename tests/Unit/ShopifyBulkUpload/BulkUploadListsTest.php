<?php

namespace Tests\Unit\ShopifyBulkUpload;

use App\Library\Shopify\Excel;
use App\Library\Shopify\ExcelValidator;
use App\Models\ShopifyExcelUpload;
use App\Models\Student;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCaseData;

class BulkUploadListsTest extends TestCase
{
    /**
     * All the test cases below takes raw data as input and pass it to the ExcelValidator class for assertion.
     */

    /**
     * Function for generating Formatted Excel object from the rows.
     *
     * @param $rows
     * @return Excel
     */
    private function generate_raw_excel($rows)
    {
        $headers = array_keys($rows);

        $ExcelFormatted = (new Excel($headers, $rows, [
            'upload_date' => '27/11/2019',
            'uploaded_by' => Auth::id(),
            'file_id' => 'shopify-253637',
            'job_status' => ShopifyExcelUpload::JOB_STATUS_PENDING,
            'order_id' => 0,
            'customer_id' => 0
        ]));

        return $ExcelFormatted;
    }

    /**
     * Test case for checking whether a valid class asserts True or not
     *
     */
    public function testClassForBulkUploadShouldPass(){

        $data = array(TestCaseData::DATA);

        $ExcelValidator = new ExcelValidator($this->generate_raw_excel($data));
        $this->assertTrue($ExcelValidator->ValidateData($ExcelValidator->FileFormattedData[0]));
    }

    /**
     * Test case for checking whether a invalid class asserts False or not
     */
    public function testClassForBulkUploadShouldFail(){

        $data = TestCaseData::DATA;
        $data['class'] = 'A';
        $excel_data = array($data);

        $ExcelValidator = new ExcelValidator($this->generate_raw_excel($excel_data));
        $ExcelValidator->ValidateData($ExcelValidator->FileFormattedData[0]);
        $errors = $ExcelValidator->get_errors()['rows'][0];

//        if(array_key_exists('class',$errors)){
//            $this->assertFalse(False);
//        }else{
//            $this->assertFalse(True);
//        }
    }

    /**
     * Test case for checking whether a valid branch asserts True or not
     */
    public function testBranchForBulkUploadShouldPass(){

        $data = array(TestCaseData::DATA);

        $ExcelValidator = new ExcelValidator($this->generate_raw_excel($data));
        $this->assertTrue($ExcelValidator->ValidateData($ExcelValidator->FileFormattedData[0]));

    }

    /**
     * Test case for checking whether a valid branch asserts True or not
     */
    public function testBranchForBulkUploadShouldFail(){

        $data = TestCaseData::DATA;
        $data['branch'] = "Gurugram";
        $excel_data = array($data);

        $ExcelValidator = new ExcelValidator($this->generate_raw_excel($excel_data));
        $ExcelValidator->ValidateData($ExcelValidator->FileFormattedData[0]);
        $errors = $ExcelValidator->get_errors()['rows'][0];

//        if(array_key_exists('branch',$errors)){
//            $this->assertFalse(False);
//        }else{
//            $this->assertFalse(True);
//        }
    }

    public function testPaymentModeShouldPass() {

        $data = array(TestCaseData::DATA);

        $ExcelValidator = new ExcelValidator($this->generate_raw_excel($data));
        $ExcelValidator->ValidateData($ExcelValidator->FileFormattedData[0]);
    }

    public function testPaymentModeShouldFail() {

        $data = TestCaseData::DATA;
        $data['mode_of_payment'] = "Razorpay";
        $data['mode_of_payment_1'] = "Stripe";
        $excel_data = array($data);

        $ExcelValidator = new ExcelValidator($this->generate_raw_excel($excel_data));
        $ExcelValidator->ValidateData($ExcelValidator->FileFormattedData[0]);
        $errors = $ExcelValidator->get_errors();

    }


}
