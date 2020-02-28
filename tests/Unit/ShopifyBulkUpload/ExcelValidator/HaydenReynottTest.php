<?php

namespace Tests\Unit\ShopifyBulkUpload;

use App\Library\Shopify\Errors;
use App\Library\Shopify\Excel;
use App\Library\Shopify\ExcelValidator;
use App\Models\ShopifyExcelUpload;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;
use Tests\TestCaseData;

class HaydenReynottTest extends TestCase
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
     * I/P - Incorrect class for Hayden & reynott academy along with other institution details
     *
     * Expected O/P - Test case should assert True if the class is incorrect according to the Hayden & Reynott Academy and the
     * error is matched.
     *
     */
    public function testIncorrectClassShouldFail()
    {
        $error = "";
        $data = TestCaseData::DATA;
        $data['class'] = "6";
        $data['section'] = "H&R";
        $data['delivery_institution'] = "H&R";
        $data['branch'] = "Plot 23 Gurugram";
        $excel_data = array($data);

        $ExcelValidator = new ExcelValidator(TestCaseData::Generate_Raw_Excel($excel_data));
        $ExcelValidator->ValidateHaydenReynottData($ExcelValidator->FileFormattedData[0]);

        if (!empty($ExcelValidator->get_errors())) {
            $error = implode(',', head(array_values($ExcelValidator->get_errors()['rows'])));
        }
        $this->assertTrue($error == Errors::HAYDEN_REYNOTT_CLASS_ERROR);

    }

    /**
     * Purpose: To check whether the function returns error on passing incorrect section or not.
     *
     * I/P - Incorrect section for hayden & reynott academy along with other institution details
     *
     * Expected O/P - Test case should assert True if the section is incorrect according to the Hayden and Reynott Academy and the
     * error is matched.
     *
     */
    public function testIncorrectSectionShouldFail()
    {
        $error = "";
        $data = TestCaseData::DATA;
        $data['class'] = "10";
        $data['section'] = "reynott";
        $data['delivery_institution'] = "H&R";
        $data['branch'] = "Dwarka";
        $excel_data = array($data);

        $ExcelValidator = new ExcelValidator(TestCaseData::Generate_Raw_Excel($excel_data));
        $ExcelValidator->ValidateHaydenReynottData($ExcelValidator->FileFormattedData[0]);

        if (!empty($ExcelValidator->get_errors())) {
            $error = implode(',', head(array_values($ExcelValidator->get_errors()['rows'])));
        }

        $this->assertTrue($error == Errors::HAYDEN_REYNOTT_SECTION_ERROR);
    }

    /**
     * Test case for validating that correct entries for class, section, delivery institution and branch.
     * Test case will assert empty if no error found.
     */
    public function testCorrectDetailsShouldPass()
    {
        $data = TestCaseData::DATA;
        $data['class'] = "9";
        $data['section'] = "H&R";
        $data['delivery_institution'] = "H&R";
        $data['branch'] = "Dwarka";
        $excel_data = array($data);

        $ExcelValidator = new ExcelValidator(TestCaseData::Generate_Raw_Excel($excel_data));
        $ExcelValidator->ValidateHaydenReynottData($ExcelValidator->FileFormattedData[0]);

        $this->assertEmpty($ExcelValidator->get_errors());
    }

}
