<?php

namespace Tests\Unit\ShopifyBulkUpload;

use App\Library\Shopify\Errors;
use App\Library\Shopify\Excel;
use App\Library\Shopify\ExcelValidator;
use App\Models\ShopifyExcelUpload;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;
use Tests\TestCaseData;

class LaravelValidatorTest extends TestCase {

    /**
     * @param $rows
     * @return Excel
     */
    private function generate_raw_excel($rows)
    {
        $headers = array_keys($rows);

        return (new Excel($headers, $rows, [
            'upload_date' => '27/11/2019',
            'uploaded_by' => Auth::id(),
            'file_id' => 'shopify-253637',
            'job_status' => ShopifyExcelUpload::JOB_STATUS_PENDING,
            'order_id' => 0,
            'customer_id' => 0
        ]));
    }

    /**
     * Test case for checking whether valid data in all the fields asserts True or not.
     *
     * I/P - Valid data in all the fields.
     * O/P - Test case will assert True if all the validations are passed else False will be returned upon execution.
     */
    public function testValidFieldsShouldPass()
    {
        $data = array(TestCaseData::DATA);
        $ExcelValidator = new ExcelValidator($this->generate_raw_excel($data));
        $this->assertTrue($ExcelValidator->ValidateData($ExcelValidator->FileFormattedData[0]));
    }

    public function testForFlatFieldsShouldFail(){
        
    }
}
