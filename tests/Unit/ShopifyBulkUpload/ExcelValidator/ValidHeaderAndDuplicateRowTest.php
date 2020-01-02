<?php

namespace Tests\Unit;

use App\Library\Shopify\ExcelValidator;
use App\Models\ShopifyExcelUpload;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Tests\TestCase;
use Tests\TestCaseData;

class ValidHeaderAndDuplicateRowTest extends TestCase
{

    private function generate_raw_excel($rows)
    {
        $headers = array_keys($rows[0]);

        return (new \App\Library\Shopify\Excel($headers, $rows, [
            'upload_date' => '27/11/2019',
            'uploaded_by' => Auth::id(),
            'file_id' => 'shopify-67587',
            'job_status' => ShopifyExcelUpload::JOB_STATUS_PENDING,
            'order_id' => 0,
            'customer_id' => 0
        ]));
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
}
