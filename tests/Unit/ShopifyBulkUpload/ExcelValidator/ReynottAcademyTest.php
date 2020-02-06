<?php

namespace Tests\Unit\ShopifyBulkUpload;

use App\Library\Shopify\Errors;
use App\Library\Shopify\Excel;
use App\Library\Shopify\ExcelValidator;
use App\Models\ShopifyExcelUpload;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;
use Tests\TestCaseData;

class ReynottAcademyTest extends TestCase
{
    /**
     * Test cases for Reynott Academy data
     *
     * This function takes rows (array) as input and return instance of the Class Excel.
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

    /**
     * Purpose: To check whether the function returns error on passing incorrect class or not.
     *
     * I/P - Incorrect class for reynott academy along with other institution details
     *
     * Expected O/P - Test case should assert True if the class is incorrect according to the Reynott Academy and the
     * error is matched.
     *
     */
    public function testIncorrectClassShouldFail()
    {
        $error = "";
        $data = TestCaseData::DATA;
        $data['class'] = "6";
        $data['section'] = "A";
        $data['delivery_institution'] = "Reynott";
        $data['branch'] = "Reynott Academy Jalandhar";
        $excel_data = array($data);

        $ExcelValidator = new ExcelValidator(TestCaseData::Generate_Raw_Excel($excel_data));
        $ExcelValidator->ValidateFieldValues($ExcelValidator->FileFormattedData[0]);

        if(!empty($ExcelValidator->get_errors())) {
            $error = implode(',', head(array_values($ExcelValidator->get_errors()['rows'])));
        }
        $this->assertTrue($error == Errors::REYNOTT_CLASS_ERROR);

    }

    /**
     * Purpose: To check whether the function returns error on passing incorrect section or not.
     *
     * I/P - Incorrect section for reynott academy along with other institution details
     *
     * Expected O/P - Test case should assert True if the section is incorrect according to the Reynott Academy and the
     * error is matched.
     *
     */
    public function testIncorrectSectionShouldFail()
    {
        $error = "";
        $data = TestCaseData::DATA;
        $data['class'] = "10";
        $data['section'] = "reynott";
        $data['delivery_institution'] = "Reynott";
        $data['branch'] = "Reynott Academy Jalandhar";
        $excel_data = array($data);

        $ExcelValidator = new ExcelValidator(TestCaseData::Generate_Raw_Excel($excel_data));
        $ExcelValidator->ValidateFieldValues($ExcelValidator->FileFormattedData[0]);

        if(!empty($ExcelValidator->get_errors())) {
            $error = implode(',', head(array_values($ExcelValidator->get_errors()['rows'])));
        }

        $this->assertTrue($error == Errors::REYNOTT_SECTION_ERROR);
    }

    /**
     * Purpose: To check whether the function returns error on passing incorrect section for specific class.
     *
     * I/P - Incorrect section for reynott academy when the class is either Dropper or Crash.
     *
     * Expected O/P - Test case should assert True if the section is other than Reynott for Dropper and Crash classes
     * and error is matched.
     *
     */
    public function testClassSectionInterdependence()
    {
        $error = "";
        $data = TestCaseData::DATA;
        $data['class'] = "Dropper";
        $data['section'] = "F";
        $data['delivery_institution'] = "Reynott";
        $data['branch'] = "Reynott Academy Jalandhar";
        $excel_data = array($data);

        $ExcelValidator = new ExcelValidator(TestCaseData::Generate_Raw_Excel($excel_data));
        $ExcelValidator->ValidateFieldValues($ExcelValidator->FileFormattedData[0]);

        if(!empty($ExcelValidator->get_errors())) {
            $error = implode(',', head(array_values($ExcelValidator->get_errors()['rows'])));
        }

        $this->assertTrue($error == Errors::REYNOTT_INTERDEPENDENCE_ERROR);
    }

    /**
     * Purpose: To check whether the function returns error on passing incorrect order type for Apeejay school.
     *
     * I/P - Incorrect order type for Apeejay school student.
     *
     * Expected O/P - Test case should assert True if the order type is external for schools under Apeejay and the error
     * is matched.
     */
    public function testIncorrectApeejayOrderShouldFail()
    {
        $error = "";
        $data = TestCaseData::DATA;
        $data['class'] = "8";
        $data['section'] = "C";
        $data['school_name'] = "Apeejay Pitampura";
        $data['external_internal'] = "External";
        $excel_data = array($data);

        $ExcelValidator = new ExcelValidator(TestCaseData::Generate_Raw_Excel($excel_data));
        $ExcelValidator->ValidateInternalExternalOrderType($ExcelValidator->FileFormattedData[0]);

        if(!empty($ExcelValidator->get_errors())) {
            $error = implode(',', head(array_values($ExcelValidator->get_errors()['rows'])));
        }

        $this->assertTrue($error == Errors::INCORRECT_APEEJAY_ORDER);
    }

    /**
     * Purpose: To check whether the function returns error on passing incorrect order type for Non-Apeejay school.
     *
     * I/P - Incorrect order type for Non-Apeejay school student.
     *
     * Expected O/P - Test case should assert True if the order type is internal for schools outside Apeejay and
     * the error is matched.
     */
    public function testIncorrectNonApeejayOrderShouldFail()
    {
        $error = "";
        $data = TestCaseData::DATA;
        $data['class'] = "8";
        $data['section'] = "C";
        $data['school_name'] = "Delhi Public School";
        $data['external_internal'] = "Internal";
        $excel_data = array($data);

        $ExcelValidator = new ExcelValidator(TestCaseData::Generate_Raw_Excel($excel_data));
        $ExcelValidator->ValidateInternalExternalOrderType($ExcelValidator->FileFormattedData[0]);

        if(!empty($ExcelValidator->get_errors())) {
            $error = implode(',', head(array_values($ExcelValidator->get_errors()['rows'])));
        }

        $this->assertTrue($error == Errors::INCORRECT_NON_APEEJAY_ORDER);
    }

    /**
     * Purpose: To check whether the function returns empty on passing Reynott as a delivery institution.
     *
     * I/P - Reynott as delivery institution
     *
     * Expected O/P - Test case should assert empty if Reynott is a valid Delivery Institution as per application.
     */
    public function testReynottDeliveryInstitution() {

        $data = TestCaseData::DATA;
        $data['delivery_institution'] = "Reynott";
        $data['branch'] = "Reynott Academy Jalandhar";
        $data['class'] = "8";
        $data['section'] = "C";
        $excel_data = array($data);

        $ExcelValidator = new ExcelValidator(TestCaseData::Generate_Raw_Excel($excel_data));
        $ExcelValidator->ValidateFieldValues($ExcelValidator->FileFormattedData[0]);
        $this->assertEmpty($ExcelValidator->get_errors());
    }

}

