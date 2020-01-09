<?php

namespace Tests\Unit\ShopifyBulkUpload;

use App\Library\Shopify\Errors;
use App\Library\Shopify\Excel;
use App\Library\Shopify\ExcelValidator;
use App\Models\ShopifyExcelUpload;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Tests\TestCase;
use Tests\TestCaseData;

class LaravelValidatorTest extends TestCase
{
    /**
     * Function for returning string, numeric and required errors for flat fields.
     *
     * Function takes field name and the validation imposed on it as input and returns the error thus generated by
     * the ExcelValidator.
     *
     * @param $field_name
     * @param $field_validation
     * @return array
     */
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
        $ExcelValidator = new ExcelValidator(TestCaseData::Generate_Raw_Excel(array($data)));
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
        $ExcelValidator = new ExcelValidator(TestCaseData::Generate_Raw_Excel($data));
        $this->assertTrue($ExcelValidator->ValidateData($ExcelValidator->FileFormattedData[0]));
    }

    /**
     * Test case for checking whether all the empty fields return required error or not.
     *
     * I/P - Empty fields
     * O/P - Test case will assert True for each field separately if error is matched else will return False.
     */
    public function testFlatFieldRequiredErrors()
    {
        $error = "";
        $rule = 'required';
        foreach (TestCaseData::REQUIRED_FLAT_FIELDS as $field_name) {

            if(!empty($this->errors_per_field_validation($field_name, $rule))) {
                $error = head(head($this->errors_per_field_validation($field_name, $rule)['rows']));
            }
            $this->assertTrue(Str::contains($error, str_replace('_',' ',$field_name)) && Str::contains($error, $rule));
        }
    }

    /**
     * Test case for checking whether all the string fields with numeric data return string error or not.
     *
     * I/P - String type fields with numeric data
     * O/P - Test case will assert True for each field separately if error is matched else will return False.
     */
    public function testFlatFieldStringErrors()
    {
        $error = "";
        $rule = 'string';
        foreach(TestCaseData::STRING_FLAT_FIELDS as $field_name) {

            if(!empty($this->errors_per_field_validation($field_name, $rule))) {
                $error = head(head($this->errors_per_field_validation($field_name, $rule)['rows']));
            }
            $this->assertTrue(Str::contains($error, str_replace('_',' ',$field_name)) && Str::contains($error, $rule));
        }
    }

    /**
     * Test case for checking whether all the numeric fields with string data return number error or not.
     *
     * I/P - Numeric type fields with string data
     * O/P - Test case will assert True for each field separately if error is matched else will return False.
     */
    public function testFlatFieldNumericErrors()
    {
        $error = "";
        $rule = 'numeric';
        $name = "number";
        foreach(TestCaseData::NUMERIC_FLAT_FIELDS as $field_name) {

            if(!empty($this->errors_per_field_validation($field_name, $rule))) {
                $error = head(head($this->errors_per_field_validation($field_name, $rule)['rows']));
            }
            $this->assertTrue(Str::contains($error, str_replace('_',' ',$field_name)) && Str::contains($error, $name));
        }
    }

    /**
     * Test case for checking the format of date for date of enrollment field.
     *
     * Function takes date of enrollment field as input and returns True if the error returned by the ExcelValidator
     * is correct else False.
     */
    public function testDateFormatErrors(){

        $error = "";
        $data = TestCaseData::DATA;
        $field_name = "date_of_enrollment";
        $data[$field_name] = "02/01/20";
        $name = "format";

        $ExcelValidator = new ExcelValidator(TestCaseData::Generate_Raw_Excel(array($data)));
        $ExcelValidator->ValidateData($ExcelValidator->FileFormattedData[0]);

        if(!empty($ExcelValidator->get_errors())) {
            $error = head(head($ExcelValidator->get_errors()['rows']));
        }
        $this->assertTrue(Str::contains($error, str_replace('_',' ',$field_name)) && Str::contains($error, $name));

    }

    /**
     * Test case for checking the mobile number format.
     *
     * Function takes mobile number as input and returns True if the error returned by the validator is correct else
     * False.
     */
    public function testMobileFormatError()
    {
        $error = "";
        $data = TestCaseData::DATA;
        $field_name = "mobile_number";
        $data[$field_name] = "+917490093267";
        $name = "format";

        $ExcelValidator = new ExcelValidator(TestCaseData::Generate_Raw_Excel(array($data)));
        $ExcelValidator->ValidateData($ExcelValidator->FileFormattedData[0]);

        if(!empty($ExcelValidator->get_errors())) {
            $error = head(head($ExcelValidator->get_errors()['rows']));
        }
        $this->assertTrue(Str::contains($error, str_replace('_',' ',$field_name)) && Str::contains($error, $name));

    }

    /**
     * Test case for checking the email id format.
     *
     * Function takes email id as input and returns True if the error returned by the validator is correct else
     * False.
     */
    public function testEmailFormatError()
    {
        $error = "";
        $data = TestCaseData::DATA;
        $field_name = "email_id";
        $data[$field_name] = "hello.com";
        $name = "valid email address";

        $ExcelValidator = new ExcelValidator(TestCaseData::Generate_Raw_Excel(array($data)));
        $ExcelValidator->ValidateData($ExcelValidator->FileFormattedData[0]);

        if(!empty($ExcelValidator->get_errors())) {
            $error = head(head($ExcelValidator->get_errors()['rows']));
        }
        $this->assertTrue(Str::contains($error, str_replace('_',' ',$field_name)) && Str::contains($error, $name));

    }

    /**
     * Test case for checking that whether the flat amount fields with errors return true or not.
     *
     * I/P - Amount fields with incorrect format
     * O/P - Test case will assert True for each field separately if error is matched else will return False.
     */
    public function testAmountFormat(){

        $error = "";
        $name = 'standard amount format';

        foreach(TestCaseData::AMOUNT_FLAT_FIELDS as $field) {
            $data = TestCaseData::DATA;
            $data[$field] = 2000.4473;
            $ExcelValidator = new ExcelValidator(TestCaseData::Generate_Raw_Excel(array($data)));
            $ExcelValidator->ValidateData($ExcelValidator->FileFormattedData[0]);

            if(!empty($ExcelValidator->get_errors())) {
                $error = head(head($ExcelValidator->get_errors()['rows']));
            }
            $this->assertTrue(Str::contains($error,str_replace('_',' ',$field)) && Str::contains($error,$name));
        }
    }

    /**
     * Test case for checking that whether the list value fields with errors return true or not.
     *
     * I/P - Flat list value fields with the value not present in the list.
     * O/P - Test case will assert True for each field separately if error is matched else will return False.
     */
    public function testFlatListFields(){

        $error = "";
        $name = 'invalid';

        foreach(TestCaseData::RULE_IN_FIELDS as $field){
            $data = TestCaseData::DATA;
            $data[$field] = "AD";

            $ExcelValidator = new ExcelValidator(TestCaseData::Generate_Raw_Excel(array($data)));
            $ExcelValidator->ValidateData($ExcelValidator->FileFormattedData[0]);

            if(!empty($ExcelValidator->get_errors())) {
                $error = head(head($ExcelValidator->get_errors()['rows']));
            }
            $this->assertTrue(Str::contains($error,$field) && Str::contains($error,$name));
        }
    }

    /**
     * Test case for checking that whether the nested numeric fields with errors return true or not.
     *
     * I/P - Nested Numeric fields
     * O/P - Test case will assert True for each field separately if error is matched else will return False.
     */
    public function testNestedNumericFields(){

        $error = "";
        $payment_no = 1;
        $name = "number";

        foreach(TestCaseData::NESTED_NUMERIC_FIELDS as $field){

            $data = TestCaseData::DATA;
            $data[$field.'_'.$payment_no] = "asd";

            $ExcelValidator = new ExcelValidator(TestCaseData::Generate_Raw_Excel(array($data)));
            $ExcelValidator->ValidateData($ExcelValidator->FileFormattedData[0]);

            if(!empty($ExcelValidator->get_errors())){
                $error = head(head($ExcelValidator->get_errors()['rows']));
            }

            $field_name = sprintf('payments.%s.%s',$payment_no,$field);
            $this->assertTrue(Str::contains($error,$field_name) && Str::contains($error,$name));

        }
    }

    /**
     * Test case for checking the mode of payment error for first payment .
     *
     * Function takes empty mode for payment 1 and returns True if the error returned by the validator is correct else
     * False.
     */
    public function testNestedModeOfPaymentRequired(){

        $error = "";
        $payment_no = 0;
        $name = "required";
        $field = "mode_of_payment";
        $data = TestCaseData::DATA;
        $data[$field] = " ";

        $ExcelValidator = new ExcelValidator(TestCaseData::Generate_Raw_Excel(array($data)));
        $ExcelValidator->ValidateData($ExcelValidator->FileFormattedData[0]);

        if(!empty($ExcelValidator->get_errors())){
            $error = head(head($ExcelValidator->get_errors()['rows']));
        }
        $field_name = sprintf('payments.%s.%s',$payment_no,$field);
        $this->assertTrue(Str::contains($error,$field_name) && Str::contains($error,$name));
    }

    /**
     * Test case for checking the incorrect drawee account number throws string format error or not.
     *
     * Function takes incorrect drawee name for payment 1 and returns True if the error returned by the validator is correct else
     * False.
     */
    public function testNestedDraweeNameString(){

        $error = "";
        $payment_no = 1;
        $name = "string";
        $field = "drawee_name";
        $data = TestCaseData::DATA;
        $data[$field.'_'.$payment_no] = 123456;

        $ExcelValidator = new ExcelValidator(TestCaseData::Generate_Raw_Excel(array($data)));
        $ExcelValidator->ValidateData($ExcelValidator->FileFormattedData[0]);

        if(!empty($ExcelValidator->get_errors())){
            $error = head(head($ExcelValidator->get_errors()['rows']));
        }
        $field_name = sprintf('payments.%s.%s',$payment_no,$field);
        $this->assertTrue(Str::contains($error,$field_name) && Str::contains($error,$name));
    }

    /**
     * Test case for checking that whether the nested numeric fields with errors return true or not.
     *
     * I/P - Nested Numeric fields with exponential values
     * O/P - Test case will assert True for each field separately if error is matched else will return False.
     */
    public function testNestedNotExponentialError(){

        $error = "";
        $payment_no = 1;
        $name = "exponential value";

        foreach(array_diff(TestCaseData::NESTED_NUMERIC_FIELDS,['amount']) as $field) {

            $data = TestCaseData::DATA;
            $data[$field.'_'.$payment_no] = "4.675E+13";

            $ExcelValidator = new ExcelValidator(TestCaseData::Generate_Raw_Excel(array($data)));
            $ExcelValidator->ValidateData($ExcelValidator->FileFormattedData[0]);

            if(!empty($ExcelValidator->get_errors())){
                $error = head(head($ExcelValidator->get_errors()['rows']));
            }
            $field_name = sprintf('payments.%s.%s', $payment_no, $field);
            $this->assertTrue(Str::contains($error, $field_name) && Str::contains($error, $name));
        }
    }

    /**
     * Test case for checking the invalid mode of payment error for.
     *
     * Function takes invalid mode for payment 1 and returns True if the error returned by the validator is correct else
     * False.
     */
    public function testModeOfPaymentInvalidValue(){

        $error = "";
        $payment_no = 0;
        $name = "invalid";
        $field = "mode_of_payment";
        $data = TestCaseData::DATA;
        $data[$field] = "Razorpay";

        $ExcelValidator = new ExcelValidator(TestCaseData::Generate_Raw_Excel(array($data)));
        $ExcelValidator->ValidateData($ExcelValidator->FileFormattedData[0]);

        if(!empty($ExcelValidator->get_errors())){
            $error = head(head($ExcelValidator->get_errors()['rows']));
        }
        $field_name = sprintf('payments.%s.%s',$payment_no,$field);
        $this->assertTrue(Str::contains($error,$field_name) && Str::contains($error,$name));
    }

    /**
     * Test case for checking the format of date for date of enrollment field.
     *
     * Function takes date of enrollment field as input and returns True if the error returned by the ExcelValidator
     * is correct else False.
     */
    public function testChequeDDateFormatErrors(){

        $error = "";
        $payment_no = 0;
        $data = TestCaseData::DATA;
        $field = "chequedd_date";
        $data[$field] = "02/01/20";
        $name = "invalid";

        $ExcelValidator = new ExcelValidator(TestCaseData::Generate_Raw_Excel(array($data)));
        $ExcelValidator->ValidateData($ExcelValidator->FileFormattedData[0]);

        if(!empty($ExcelValidator->get_errors())) {
            $error = head(head($ExcelValidator->get_errors()['rows']));
        }
        $field_name = sprintf('payments.%s.%s',$payment_no,$field);
        $this->assertTrue(Str::contains($error, $field_name) && Str::contains($error, $name));

    }
}
