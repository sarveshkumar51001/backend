<?php

namespace Tests\Unit;
use App\Http\Controllers\ShopifyController;
use App\Library\Shopify\ExcelValidator;
use App\Models\ShopifyExcelUpload;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     *
     * @return void
     */
    public function testBasicTest()
    {
        $this->assertTrue(true);
    }

    // Checking whether the reader is instance of Maatwebsite RowCollection
    public function testExcelLoading()
    {
        $ExlReader = Excel::load('/var/www/html/backend/storage/uploads/test_file1.xlsx')->get()->first();
        $this->assertInstanceOf('Maatwebsite\Excel\Collections\RowCollection',$ExlReader);
    }

    // Getting Excel file for running Excel Validator test cases
    private function getExcel($path)
    {
        config([
            'excel.import.startRow' => 2,
            'excel.import.heading' => 'slugged_with_count',
            'excel.import.dates.enabled' => false,
            'excel.import.force_sheets_collection' => true
        ]);

        $ExlReader = Excel::load($path)->get()->first();
        $header = $ExlReader->first()->keys()->toArray();

        $ExcelRaw = (new \App\Library\Shopify\Excel($header, $ExlReader->toArray(), [
            'upload_date' => '20/11/2019',
            'uploaded_by' => 'Test',
            'file_id' => 'file-09099',
            'job_status' => ShopifyExcelUpload::JOB_STATUS_PENDING,
            'order_id' => 0,
            'customer_id' => 0
        ]));

        return $ExcelRaw;
    }

    // Test case should assert True if file headers are valid
    public function testHasAllValidHeadersShouldPass() {

        $path = '/var/www/html/backend/storage/uploads/test_file1.xlsx';

        $ExcelValidator = new ExcelValidator($this->getExcel($path));
        $this->assertTrue($ExcelValidator->HasAllValidHeaders());

    }

    // Test case should assert False if the file headers are invalid
    public function testHasAllValidHeadersShouldFail() {

        $path = '/var/www/html/backend/storage/uploads/test_file1.xlsx';

        $ExcelValidator = new ExcelValidator($this->getExcel($path));
        $this->assertFalse($ExcelValidator->HasAllValidHeaders());

    }

    // Test case should assert true if row is found to be duplicate
    public function testHasDuplicateRowPass() {

        $path = '/var/www/html/backend/storage/uploads/test_file1.xlsx';

        $ExcelValidator = new ExcelValidator($this->getExcel($path));
        $this->assertTrue($ExcelValidator->ValidateDuplicateRow($ExcelValidator->FileFormattedData[0]));
    }

    // Test case should assert False if the row is not found to be duplicate.
    public function testHasDuplicateRowFail()
    {
        $path = '/var/www/html/backend/storage/uploads/test_file1.xlsx';

        $ExcelValidator = new ExcelValidator($this->getExcel($path));
        $this->assertFalse($ExcelValidator->ValidateDuplicateRow($ExcelValidator->FileFormattedData[0]));

    }

    // Test case should assert True if all the field values are correct
    public function testHasValidFieldValuesShouldPass()
    {
        $path = '/var/www/html/backend/storage/uploads/test_file1.xlsx';

        $ExcelValidator = new ExcelValidator($this->getExcel($path));
        $this->assertTrue($ExcelValidator->ValidateData($ExcelValidator->FileFormattedData[0]));

    }

    // Test case should assert False if any of the field value is incorrect
    public function testHasValidFieldValuesShouldFail()
    {
        $path = '/var/www/html/backend/storage/uploads/test_file1.xlsx';

        $ExcelValidator = new ExcelValidator($this->getExcel($path));
        $this->assertFalse($ExcelValidator->ValidateData($ExcelValidator->FileFormattedData[0]));
    }
}
