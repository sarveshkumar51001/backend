<?php

namespace Tests\Unit\ShopifyBulkUpload;

use App\Library\Shopify\Errors;
use App\Library\Shopify\Excel;
use App\Library\Shopify\ExcelValidator;
use App\Models\ShopifyExcelUpload;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Tests\TestCase;
use Tests\TestCaseData;

class LaravelValidatorTest extends TestCase
{

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

    private function errors_per_field_validation($field_name,$field_validation)
    {
        $data = TestCaseData::DATA;
        if ($field_validation == 'required') {
            $data[$field_name] = "";
        } else if ($field_validation == 'string') {
            unset($data[$field_name]);
            $data[$field_name] = 45453432;
        } else if ($field_validation == 'numeric') {
            $data[$field_name] = "TestData";
        }
        $ExcelValidator = new ExcelValidator($this->generate_raw_excel(array($data)));
        $ExcelValidator->ValidateData($ExcelValidator->FileFormattedData[0]);
        return $ExcelValidator->get_errors();
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

    /**
     * Test case for checking whether all the empty fields return required error or not.
     *
     * I/P - Empty fields
     * O/P - Test case will assert True if all the errors returned by the ExcelValidator else False.
     */
    public function testFlatFieldRequiredErrors()
    {
        $rule = 'required';
        foreach (TestCaseData::REQUIRED_FLAT_FIELDS as $field_name) {
            $error = head(head($this->errors_per_field_validation($field_name, $rule)['rows']));
            $this->assertTrue(Str::contains($error, str_replace('_',' ',$field_name)) && Str::contains($error, $rule));
        }
    }

    /**
     * Test case for checking whether all the string fields with numeric data return string error or not.
     *
     * I/P - String type fields with numeric data
     * O/P - Test case will assert True if all the errors returned by the ExcelValidator are correct else False.
     */
    public function testStringErrors()
    {
        $rule = 'string';
        foreach(TestCaseData::STRING_FIELDS as $field_name) {
            $error = head(head($this->errors_per_field_validation($field_name, $rule)['rows']));
            $this->assertTrue(Str::contains($error, str_replace('_',' ',$field_name)) && Str::contains($error, $rule));
        }
    }

    /**
     * Test case for checking whether all the numeric fields with string data return number error or not.
     *
     * I/P - Numeric type fields with string data
     * O/P - Test case will assert True if all the errors returned by the ExcelValidator are correct else False.
     */
    public function testNumericErrors()
    {
        $rule = 'numeric';
        $name = "number";
        foreach(TestCaseData::NUMERIC_FIELDS as $field_name) {
            $error = head(head($this->errors_per_field_validation($field_name, $rule)['rows']));
            $this->assertTrue(Str::contains($error, str_replace('_',' ',$field_name)) && Str::contains($error, $name));
        }
    }

    /**
     *
     */
    public function testDateFormatErrors(){
        $data = TestCaseData::DATA;
        $field_name = "date_of_enrollment";
        $data[$field_name] = "02/01/20";
        $name = "format";

        $ExcelValidator = new ExcelValidator($this->generate_raw_excel(array($data)));
        $ExcelValidator->ValidateData($ExcelValidator->FileFormattedData[0]);
        $error = head(head($ExcelValidator->get_errors()['rows']));
        $this->assertTrue(Str::contains($error, str_replace('_',' ',$field_name)) && Str::contains($error, $name));

    }

    /**
     *
     */
    public function testMobileFormatErrors(){
        $data = TestCaseData::DATA;
        $field_name = "mobile_number";
        $data[$field_name] = "+917490093267";
        $name = "format";

        $ExcelValidator = new ExcelValidator($this->generate_raw_excel(array($data)));
        $ExcelValidator->ValidateData($ExcelValidator->FileFormattedData[0]);
        $error = head(head($ExcelValidator->get_errors()['rows']));
        $this->assertTrue(Str::contains($error, str_replace('_',' ',$field_name)) && Str::contains($error, $name));

    }
}
