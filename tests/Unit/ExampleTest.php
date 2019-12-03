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
