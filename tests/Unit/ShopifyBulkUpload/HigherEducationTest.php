<?php

namespace Tests\Unit\ShopifyBulkUpload;

use App\Library\Shopify\Excel;
use App\Library\Shopify\ExcelValidator;
use App\Models\ShopifyExcelUpload;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCaseData;

class HigherEducationTest extends TestCase
{
    /**
     * Test case for Higher Education related validations
     *
     * Function takes data rows as input and return object of class Excel with formatted data
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

    // Test case should assert Null if class and section are correct as per higher institutes.
    public function testHigherEducationPass(){

        $data = TestCaseData::DATA;
        $excel_data = array($data);

        $ExcelValidator = new ExcelValidator($this->generate_raw_excel($excel_data));
        $this->assertNull($ExcelValidator->ValidateHigherEducationData($ExcelValidator->FileFormattedData[0]));
    }

    // Test case should assert not empty if incorrect class is entered for higher institutes.
    public function testIncorrectClassForHigherEducation(){

        $data = TestCaseData::DATA;
        $data['class'] = "10";
        $data['section'] = "Sem1";
        $excel_data = array($data);

        $ExcelValidator = new ExcelValidator($this->generate_raw_excel($excel_data));
        $ExcelValidator->ValidateHigherEducationData($ExcelValidator->FileFormattedData[0]);

        $this->assertNotEmpty($ExcelValidator->get_errors());
    }

    // Test case should assert not empty if incorrect section is entered for higher institutes.
    public function testIncorrectSectionForHigherEducation(){

        $data = TestCaseData::DATA;
        $data['class'] = "BTECH";
        $data['section'] = "C";
        $excel_data = array($data);

        $ExcelValidator = new ExcelValidator($this->generate_raw_excel($excel_data));
        $ExcelValidator->ValidateHigherEducationData($ExcelValidator->FileFormattedData[0]);

        $this->assertNotEmpty($ExcelValidator->get_errors());
    }

    // Test case should assert not empty if incorrect class is entered for schools.
    public function testIncorrectClassForSchool(){

        $data = TestCaseData::DATA;
        $data['branch'] = "Saket";
        $data['class'] = "BA";
        $data['section'] = "C";
        $excel_data = array($data);

        $ExcelValidator = new ExcelValidator($this->generate_raw_excel($excel_data));
        $ExcelValidator->ValidateHigherEducationData($ExcelValidator->FileFormattedData[0]);

        $this->assertNotEmpty($ExcelValidator->get_errors());

    }

    // Test case should assert not empty if incorrect section is entered for schools.
    public function testIncorrectSectionForSchool(){

        $data = TestCaseData::DATA;
        $data['branch'] = "Pitampura";
        $data['class'] = "9";
        $data['section'] = "Sem 1";
        $excel_data = array($data);

        $ExcelValidator = new ExcelValidator($this->generate_raw_excel($excel_data));
        $ExcelValidator->ValidateHigherEducationData($ExcelValidator->FileFormattedData[0]);

        $this->assertNotEmpty($ExcelValidator->get_errors());

    }

}


