<?php

namespace Tests\Unit\ShopifyBulkUpload;

use App\Library\Shopify\Errors;
use App\Library\Shopify\Excel;
use App\Library\Shopify\ExcelValidator;
use App\Models\ShopifyExcelUpload;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;
use Tests\TestCaseData;

class ValidateFieldsAndActivityDetailsTest extends TestCase
{
    /**
     * Function takes data rows as input and return object of class Excel with formatted data
     * @param $rows
     * @return Excel
     */
    private function generate_raw_excel($rows)
    {
        $headers = array_keys($rows);

        $ExcelFormatted = (new Excel($headers, $rows, [
            'upload_date' => '02/01/2020',
            'uploaded_by' => Auth::id(),
            'file_id' => 'shopify-253637',
            'job_status' => ShopifyExcelUpload::JOB_STATUS_PENDING,
            'order_id' => 0,
            'customer_id' => 0
        ]));

        return $ExcelFormatted;
    }

    public function testIncorrectProductIDShouldFail(){

        $data = TestCaseData::DATA;
        $data['shopify_activity_id'] = "ABC-XYZ";
        $excel_data = array($data);

        $ExcelValidator = new ExcelValidator($this->generate_raw_excel($excel_data));
        $ExcelValidator->ValidateActivityDetails($ExcelValidator->FileFormattedData[0]);
        $error = implode(',',head(array_values($ExcelValidator->get_errors()['rows'])));

        $this->assertTrue($error == Errors::ACTIVITY_ID_ERROR);

    }

    public function testDuplicateActivityIDShouldFail(){

        $data = TestCaseData::DATA;
        $data['shopify_activity_id'] = "ABC-001";
        $excel_data = array($data);

        $ExcelValidator = new ExcelValidator($this->generate_raw_excel($excel_data));
        $ExcelValidator->ValidateActivityDetails($ExcelValidator->FileFormattedData[0]);
        $error = implode(',',head(array_values($ExcelValidator->get_errors()['rows'])));
        logger($error);

        $this->assertTrue($error == Errors::DUPLICATE_ACTIVITY_ERROR);

    }
    public function testIncorrectActivityFeeShouldFail(){

        $data = TestCaseData::DATA;

        $ExcelValidator = new ExcelValidator($this->generate_raw_excel($data));
        $ExcelValidator->ValidateHigherEducationData($ExcelValidator->FileFormattedData[0]);

    }
    public function testProductOutOfStockShouldFail(){

        $data = TestCaseData::DATA;

        $ExcelValidator = new ExcelValidator($this->generate_raw_excel($data));
        $ExcelValidator->ValidateHigherEducationData($ExcelValidator->FileFormattedData[0]);

    }
    public function testCorrectActivityShouldPass(){

        $data = TestCaseData::DATA;

        $ExcelValidator = new ExcelValidator($this->generate_raw_excel($data));
        $ExcelValidator->ValidateHigherEducationData($ExcelValidator->FileFormattedData[0]);

    }

























































}
