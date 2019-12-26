<?php

namespace Tests\Unit\ShopifyBulkUpload;

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

    // Test case should assert not empty if the class is incorrect according to the Reynott Academy.
    public function testIncorrectClassShouldFail()
    {
        $data = TestCaseData::DATA;
        $data['class'] = "6";
        $data['section'] = "C";
        $data['delivery_institution'] = "Reynott";
        $data['branch'] = "Reynott Academy";
        $excel_data = array($data);

        $ExcelValidator = new ExcelValidator($this->generate_raw_excel($excel_data));
        $ExcelValidator->ValidateFieldValues($ExcelValidator->FileFormattedData[0]);
        $this->assertNotEmpty($ExcelValidator->get_errors());

    }

    // Test case should assert not empty if the section is incorrect according to the Reynott Academy.
    public function testIncorrectSectionShouldFail()
    {
        $data = TestCaseData::DATA;
        $data['class'] = "10";
        $data['section'] = "reynott";
        $excel_data = array($data);

        $ExcelValidator = new ExcelValidator($this->generate_raw_excel($excel_data));
        $ExcelValidator->ValidateFieldValues($ExcelValidator->FileFormattedData[0]);

        $this->assertNotEmpty($ExcelValidator->get_errors());
    }

    // Test case should assert not empty if the section is other than Reynott for Dropper and Crash classes.
    public function testClassSectionInterdependence()
    {
        $data = TestCaseData::DATA;
        $data['class'] = "Dropper";
        $data['section'] = "H";
        $excel_data = array($data);

        $ExcelValidator = new ExcelValidator($this->generate_raw_excel($excel_data));
        $ExcelValidator->ValidateFieldValues($ExcelValidator->FileFormattedData[0]);

        $this->assertNotEmpty($ExcelValidator->get_errors());
    }

    // Test case should assert not empty if the order type is external for schools under Apeejay.
    public function testIncorrectApeejayOrderShouldFail()
    {
        $data = TestCaseData::DATA;
        $data['class'] = "8";
        $data['section'] = "C";
        $data['school_name'] = "Apeejay Pitampura";
        $data['external_internal'] = "External";
        $excel_data = array($data);

        $ExcelValidator = new ExcelValidator($this->generate_raw_excel($excel_data));
        $ExcelValidator->ValidateInternalExternalOrderType($ExcelValidator->FileFormattedData[0]);

        $this->assertNotEmpty($ExcelValidator->get_errors());
    }

    // Test case should assert not empty if the order type is internal for schools outside Apeejay.
    public function testIncorrectNonApeejayOrderShouldFail()
    {
        $data = TestCaseData::DATA;
        $data['class'] = "8";
        $data['section'] = "C";
        $data['school_name'] = "Delhi Public School";
        $data['external_internal'] = "Internal";
        $excel_data = array($data);

        $ExcelValidator = new ExcelValidator($this->generate_raw_excel($excel_data));
        $ExcelValidator->ValidateInternalExternalOrderType($ExcelValidator->FileFormattedData[0]);

        $this->assertNotEmpty($ExcelValidator->get_errors());
    }

    public function testReynottDeliveryInstitution() {
        $data = TestCaseData::DATA;
        $data['delivery_institution'] = "Reynott";
        $excel_data = array($data);

        $ExcelValidator = new ExcelValidator($this->generate_raw_excel($excel_data));
        $ExcelValidator->ValidateFieldValues($ExcelValidator->FileFormattedData[0]);
        $this->assertEmpty($ExcelValidator->get_errors());
    }

}

