<?php

namespace Tests\Unit;
use App\Http\Controllers\ShopifyController;
use App\Library\Shopify\ExcelValidator;
use App\Models\ShopifyExcelUpload;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCaseData;

class ExcelValidatorTest extends TestCase
{

    private function generate_raw_excel($rows)
    {
        $headers = array_keys($rows[0]);

        $ExcelRaw = (new \App\Library\Shopify\Excel($headers, $rows, [
            'upload_date' => '27/11/2019',
            'uploaded_by' => Auth::id(),
            'file_id' => 'shopify-67587',
            'job_status' => ShopifyExcelUpload::JOB_STATUS_PENDING,
            'order_id' => 0,
            'customer_id' => 0
        ]));

        return $ExcelRaw;
    }

    // Test case should assert True if file headers are valid
    public function testHasAllValidHeadersShouldPass() {

        $data = TestCaseData::DATA;

        $ExcelValidator = new ExcelValidator($this->generate_raw_excel(array($data)));
        $this->assertTrue($ExcelValidator->HasAllValidHeaders());

    }

    // Test case should assert False if the file headers are invalid
    public function testHasAllValidHeadersShouldFail() {

        // Unsettled a key and replacing it with a incorrect one for test case execution.
        $data = TestCaseData::DATA;
        unset($data['date_of_enrollment']);
        $data['enrollment_date'] = "";

        $ExcelValidator = new ExcelValidator($this->generate_raw_excel(array($data)));
        $this->assertFalse($ExcelValidator->HasAllValidHeaders());

    }

    // Test case should assert true if row is found to be duplicate
    public function testHasDuplicateRowPass() {

        $data = TestCaseData::DATA;

        $ExcelValidator = new ExcelValidator($this->generate_raw_excel(array($data)));
        $this->assertTrue($ExcelValidator->ValidateDuplicateRow($ExcelValidator->FileFormattedData[0]));
    }

    // Test case should assert False if the row is not found to be duplicate.
    public function testHasDuplicateRowFail()
    {
        $data = TestCaseData::DATA;
        $data['school_enrollment_no'] = "PP-8931";

        $ExcelValidator = new ExcelValidator($this->generate_raw_excel(array($data)));
        $this->assertFalse($ExcelValidator->ValidateDuplicateRow($ExcelValidator->FileFormattedData[0]));

    }

    // Test case should assert True if all the field values are correct
    public function testHasValidFieldValuesShouldPass()
    {
        $data = TestCaseData::DATA;

        $ExcelValidator = new ExcelValidator($this->generate_raw_excel(array($data)));
        $this->assertFalse($ExcelValidator->ValidateData($ExcelValidator->FileFormattedData[0]));

    }

    // Test case should assert False if any of the field value is incorrect
    public function testHasValidFieldValuesShouldFail()
    {
        $data = TestCaseData::DATA;
        $data['date_of_enrollment'] = "";

        $ExcelValidator = new ExcelValidator($this->generate_raw_excel(array($data)));
        $this->assertTrue($ExcelValidator->ValidateData($ExcelValidator->FileFormattedData[0]));
    }

    // Test case should assert Null if the total entered while file upload matches the total in the sheet.
    public function testValidAmountShouldPass(){

        $data = TestCaseData::DATA;
        $amount_data = [
            "cash-total" => 1000,
            "cheque-total" => 2000,
            "online-total" => 3000
        ];

        $ExcelValidator = new ExcelValidator($this->generate_raw_excel($data),$amount_data);
        $this->assertNull($ExcelValidator->ValidateAmount());
    }

    // Test case should assert Not Empty of the cash total entered at the time of file upload not matches the sheet.
    public function testInvalidCashTotal(){

        $data = TestCaseData::DATA;
        $amount_data = [
            "cash-total" => 1000,
            "cheque-total" => 0,
            "online-total" => 0
        ];

        $ExcelValidator = new ExcelValidator($this->generate_raw_excel($data));
        $this->assertNotEmpty($ExcelValidator->ValidateAmount());

    }

    // Test case should assert Not Empty of the cheque total entered at the time of file upload not matches the sheet.
    public function testInvalidChequeTotal() {

        $data = TestCaseData::DATA;
        $amount_data = [
            "cash-total" => 0,
            "cheque-total" => 2000,
            "online-total" => 0
        ];

        $ExcelValidator = new ExcelValidator($this->generate_raw_excel($data));
        $this->assertNotEmpty($ExcelValidator->ValidateAmount());
    }

    // Test case should assert Not Empty of the online total entered at the time of file upload not matches the sheet.
    public function testInvalidOnlineTotal() {

        $data = TestCaseData::DATA;
        $amount_data = [
            "cash-total" => 0,
            "cheque-total" => 0,
            "online-total" => 3000
        ];

        $ExcelValidator = new ExcelValidator($this->generate_raw_excel($data));
        $this->assertNotEmpty($ExcelValidator->ValidateAmount());

    }


}
