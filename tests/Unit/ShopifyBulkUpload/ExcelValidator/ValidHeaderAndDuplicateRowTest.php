<?php

namespace Tests\Unit;

use App\Library\Shopify\Errors;
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
            'uploaded_by' => "5d1214cbafd58641b5532f82",
            'file_id' => 'shopify-67587',
            'job_status' => ShopifyExcelUpload::JOB_STATUS_PENDING,
            'order_id' => 0,
            'customer_id' => 0
        ]));
    }

    // Test case should assert True if file headers are valid
    public function testHasAllValidHeadersShouldPass()
    {

        $data = TestCaseData::DATA;
        $excel_data = array($data);

        $ExcelValidator = new ExcelValidator($this->generate_raw_excel($excel_data));
        $this->assertTrue($ExcelValidator->HasAllValidHeaders());

    }

    // Test case should assert False if the file headers are invalid
    public function testHasAllValidHeadersShouldFail()
    {
        // Unsettled a key and replacing it with a incorrect one for test case execution.
        $data = TestCaseData::DATA;
        unset($data['date_of_enrollment']);
        $data['enrollment_date'] = "";
        $excel_data = array($data);

        $ExcelValidator = new ExcelValidator($this->generate_raw_excel($excel_data));
        $this->assertFalse($ExcelValidator->HasAllValidHeaders());

    }

    // Test case should assert true if row is found to be duplicate
    public function testHasDuplicateRowPass()
    {

        $data = TestCaseData::DATA;
        $excel_data = array($data);

        $ExcelValidator = new ExcelValidator($this->generate_raw_excel($excel_data));
        $this->assertTrue($ExcelValidator->ValidateDuplicateRow($ExcelValidator->FileFormattedData[0]));
    }

    // Test case should assert False if the row is not found to be duplicate.
    public function testHasDuplicateRowFail()
    {
        $data = TestCaseData::DATA;
        $data['school_enrollment_no'] = "PP-8931";
        $excel_data = array($data);

        $ExcelValidator = new ExcelValidator($this->generate_raw_excel($excel_data));
        $this->assertFalse($ExcelValidator->ValidateDuplicateRow($ExcelValidator->FileFormattedData[0]));

    }

    // Test case should assert True if both email and mobile number are empty else false.
    public function testEmptyEmailOrContactShoudFail()
    {
        $error = "";
        $data = TestCaseData::DATA;
        $data['email_id'] = "";
        $data['mobile_number'] = "";
        $excel_data = array($data);

        $ExcelValidator = new ExcelValidator($this->generate_raw_excel($excel_data));
        $ExcelValidator->ValidateFieldValues($ExcelValidator->FileFormattedData[0]);

        if (!empty($ExcelValidator->get_errors())) {
            $error = implode(',', head(array_values($ExcelValidator->get_errors()['rows'])));
        }
        $this->assertTrue($error == Errors::CONTACT_DETAILS_ERROR);
    }

    // Test case should assert true if location is incorrect else false.
    public function testIncorrectLocationShoudFail()
    {
        $error = "";
        $data = TestCaseData::DATA;
        $data['delivery_institution'] = "Apeejay";
        $data['branch'] = "Mumbai";
        $excel_data = array($data);

        $ExcelValidator = new ExcelValidator($this->generate_raw_excel($excel_data));
        $ExcelValidator->ValidateFieldValues($ExcelValidator->FileFormattedData[0]);

        if (!empty($ExcelValidator->get_errors())) {
            $error = implode(',', head(array_values($ExcelValidator->get_errors()['rows'])));
        }

        $this->assertTrue($error == Errors::LOCATION_ERROR);
    }

    public function testAlreadyProcessedInstallmentShouldFail()
    {
        $error = "";
        $data = TestCaseData::DATA;
        $data['chequedd_no_1'] = 7004640;
        $excel_data = array($data);

        $ExcelValidator = new ExcelValidator($this->generate_raw_excel($excel_data));
        $ExcelValidator->ValidateDuplicateRow($ExcelValidator->FileFormattedData[0]);
        $index = array_search($data['mode_of_payment_1'], array_column($ExcelValidator->FileFormattedData[0]['payments'], 'mode_of_payment'));

        if (!empty($ExcelValidator->get_errors())) {
            $error = implode(',', head(array_values($ExcelValidator->get_errors()['rows'])));
        }

        $this->assertTrue($error == sprintf(Errors::PROCESSED_INSTALLMENT_ERROR, $index + 1));
    }

    public function testUpdatedFieldsShouldFail()
    {
        $error = "";
        $data = TestCaseData::DATA;
        $data['student_first_name'] = "Sahil";
        $fields_updated = ["student_first_name"];
        $excel_data = array($data);

        $ExcelValidator = new ExcelValidator($this->generate_raw_excel($excel_data));
        $ExcelValidator->ValidateDuplicateRow($ExcelValidator->FileFormattedData[0]);

        if (!empty($ExcelValidator->get_errors())) {
            $error = head(head(array_values($ExcelValidator->get_errors()['rows'])));
        }
        $this->assertTrue($error == sprintf(Errors::FIELD_UPDATED_ERROR, implode($fields_updated, ",")));

    }
}
