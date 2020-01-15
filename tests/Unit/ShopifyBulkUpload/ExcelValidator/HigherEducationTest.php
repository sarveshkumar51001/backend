<?php

namespace Tests\Unit\ShopifyBulkUpload;

use App\Library\Shopify\Errors;
use App\Library\Shopify\Excel;
use App\Library\Shopify\ExcelValidator;
use App\Models\ShopifyExcelUpload;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCaseData;

class HigherEducationTest extends TestCase
{

    // Test case should assert Null if class and section are correct as per higher institutes.
    public function testHigherEducationPass(){

        $data = TestCaseData::DATA;
        $excel_data = array($data);

        $ExcelValidator = new ExcelValidator(TestCaseData::Generate_Raw_Excel($excel_data));
        $this->assertNull($ExcelValidator->ValidateHigherEducationData($ExcelValidator->FileFormattedData[0]));
    }

    // Test case should assert not empty if incorrect class is entered for higher institutes.
    public function testIncorrectClassForHigherEducation(){

        $error = "";
        $data = TestCaseData::DATA;
        $data['class'] = "10";
        $data['branch'] = "ASM Dwarka";
        $data['section'] = "Sem 1";
        $excel_data = array($data);

        $ExcelValidator = new ExcelValidator(TestCaseData::Generate_Raw_Excel($excel_data));
        $ExcelValidator->ValidateHigherEducationData($ExcelValidator->FileFormattedData[0]);
        if(!empty($ExcelValidator->get_errors())) {
            $error = implode(',', head(array_values($ExcelValidator->get_errors()['rows'])));
        }
        $this->assertTrue($error == Errors::INSTITUTE_CLASS_ERROR);
    }

    // Test case should assert not empty if incorrect section is entered for higher institutes.
    public function testIncorrectSectionForHigherEducation(){

        $error = "";
        $data = TestCaseData::DATA;
        $data['branch'] = "ASM Dwarka";
        $data['class'] = "BTECH";
        $data['section'] = "C";
        $excel_data = array($data);

        $ExcelValidator = new ExcelValidator(TestCaseData::Generate_Raw_Excel($excel_data));
        $ExcelValidator->ValidateHigherEducationData($ExcelValidator->FileFormattedData[0]);
        if(!empty($ExcelValidator->get_errors())) {
            $error = implode(',', head(array_values($ExcelValidator->get_errors()['rows'])));
        }
        $this->assertTrue($error == Errors::INSTITUTE_SECTION_ERROR);
    }

    // Test case should assert not empty if incorrect class is entered for schools.
    public function testIncorrectClassForSchool(){

        $error = "";
        $data = TestCaseData::DATA;
        $data['branch'] = "Saket";
        $data['class'] = "BA";
        $data['section'] = "C";
        $excel_data = array($data);

        $ExcelValidator = new ExcelValidator(TestCaseData::Generate_Raw_Excel($excel_data));
        $ExcelValidator->ValidateHigherEducationData($ExcelValidator->FileFormattedData[0]);
        if(!empty($ExcelValidator->get_errors())) {
            $error = implode(',', head(array_values($ExcelValidator->get_errors()['rows'])));
        }
        $this->assertTrue($error == Errors::SCHOOL_CLASS_ERROR);

    }

    // Test case should assert not empty if incorrect section is entered for schools.
    public function testIncorrectSectionForSchool(){

        $error = "";
        $data = TestCaseData::DATA;
        $data['branch'] = "Pitampura";
        $data['class'] = "9";
        $data['section'] = "Sem 1";
        $excel_data = array($data);

        $ExcelValidator = new ExcelValidator(TestCaseData::Generate_Raw_Excel($excel_data));
        $ExcelValidator->ValidateHigherEducationData($ExcelValidator->FileFormattedData[0]);
        if(!empty($ExcelValidator->get_errors())) {
            $error = implode(',', head(array_values($ExcelValidator->get_errors()['rows'])));
        }
        $this->assertTrue($error == Errors::SCHOOL_SECTION_ERROR);

    }
}


