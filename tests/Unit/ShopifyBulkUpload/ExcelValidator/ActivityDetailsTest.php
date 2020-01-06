<?php

namespace Tests\Unit\ShopifyBulkUpload;

use App\Library\Shopify\Errors;
use App\Library\Shopify\Excel;
use App\Library\Shopify\ExcelValidator;
use App\Models\ShopifyExcelUpload;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;
use Tests\TestCaseData;

class ActivityDetailsTest extends TestCase
{

    /**
     * Testing that invalid product id in DB results in an error.
     *
     * I/P - Invalid shopify product id
     * O/P - Test case will assert True if the error returned from the ExcelValidator is same as the expected error
     * else false.
     */
    public function testIncorrectProductIDShouldFail(){

        $error = "";
        $data = TestCaseData::DATA;
        $data['shopify_activity_id'] = "ABC-XYZ";
        $excel_data = array($data);


        $ExcelValidator = new ExcelValidator(TestCaseData::Generate_Raw_Excel($excel_data));
        $ExcelValidator->ValidateActivityDetails($ExcelValidator->FileFormattedData[0]);

        if(!empty($ExcelValidator->get_errors())) {
            $error = implode(',', head(array_values($ExcelValidator->get_errors()['rows'])));
        }

        $this->assertTrue($error == Errors::ACTIVITY_ID_ERROR);

    }

    /**
     * Testing that duplicate product id in DB results in an error.
     *
     * I/P - Duplicate Activity ID
     * O/P - Test case will assert True if the error returned from the ExcelValidator is same as the expected error
     * else false.
     */
    public function testDuplicateActivityIDShouldFail(){

        $error = "";
        $data = TestCaseData::DATA;
        $data['shopify_activity_id'] = "ABC-001";
        $excel_data = array($data);

        $ExcelValidator = new ExcelValidator(TestCaseData::Generate_Raw_Excel($excel_data));
        $ExcelValidator->ValidateActivityDetails($ExcelValidator->FileFormattedData[0]);

        if(!empty($ExcelValidator->get_errors())) {
            $error = implode(',', head(array_values($ExcelValidator->get_errors()['rows'])));
        }

        $this->assertTrue($error == sprintf(Errors::DUPLICATE_ACTIVITY_ERROR,$data['shopify_activity_id']));

    }

    /**
     * Testing that incorrect product fee results in an error.
     *
     * I/P - Incorrect Activity Fee
     * O/P - Test case will assert True if the error returned from the ExcelValidator is same as the expected error
     * else false
     */
    public function testIncorrectActivityFeeShouldFail(){

        $error = "";
        $data = TestCaseData::DATA;
        $data['activity_fee'] = 6372;
        $data['final_fee_incl_gst'] = 6372;
        $excel_data = array($data);

        $ExcelValidator = new ExcelValidator(TestCaseData::Generate_Raw_Excel($excel_data));
        $ExcelValidator->ValidateActivityDetails($ExcelValidator->FileFormattedData[0]);

        if(!empty($ExcelValidator->get_errors())) {
            $error = implode(',', head(array_values($ExcelValidator->get_errors()['rows'])));
        }

        $this->assertTrue($error == Errors::ACTIVITY_FEE_ERROR);

    }

    /**
     * Testing that out of stock product results in an error.
     *
     * I/P - Out of stock product
     * O/P - Test case will assert True if the error returned from the ExcelValidator is same as the expected error
     * else false
     */
    public function testProductOutOfStockShouldFail(){

        $error = "";
        $data = TestCaseData::DATA;
        $data['shopify_activity_id'] = "ST18-SCNVD";
        $data['activity_fee'] = 1600;
        $data['final_fee_incl_gst'] = 1600;
        $excel_data = array($data);

        $ExcelValidator = new ExcelValidator(TestCaseData::Generate_Raw_Excel($excel_data));
        $ExcelValidator->ValidateActivityDetails($ExcelValidator->FileFormattedData[0]);

        if(!empty($ExcelValidator->get_errors())) {
            $error = implode(',', head(array_values($ExcelValidator->get_errors()['rows'])));
        }
        $this->assertTrue($error == Errors::OUT_OF_STOCK_ERROR);

    }

    /**
     * Testing that incorrect final fee after including GST results in an error.
     *
     * I/P - Incorrect Final Fee
     * O/P - Test case will assert True if the error returned from the ExcelValidator is same as the expected error
     * else false
     */
    public function testIncorrectFeeMismatchShouldFail(){

        $error = "";
        $data = TestCaseData::DATA;
        $data['final_fee_incl_gst'] = 63721;
        $excel_data = array($data);

        $ExcelValidator = new ExcelValidator(TestCaseData::Generate_Raw_Excel($excel_data));
        $ExcelValidator->ValidateActivityDetails($ExcelValidator->FileFormattedData[0]);

        if(!empty($ExcelValidator->get_errors())) {
            $error = implode(',', head(array_values($ExcelValidator->get_errors()['rows'])));
        }

        $this->assertTrue($error == Errors::FINAL_FEE_ERROR);

    }

    /**
     * Testing that incorrect after discount fee results in an error.
     *
     * I/P - incorrect after discount fee
     * O/P - Test case will assert True if the error returned from the ExcelValidator is same as the expected error
     * else false
     */
    public function testAfterDiscountFeeMismatchShouldFail(){

        $error = "";
        $data = TestCaseData::DATA;
        $data['scholarship_discount'] = 3720;
        $data['final_fee_incl_gst'] = 50000;
        $excel_data = array($data);

        $ExcelValidator = new ExcelValidator(TestCaseData::Generate_Raw_Excel($excel_data));
        $ExcelValidator->ValidateActivityDetails($ExcelValidator->FileFormattedData[0]);

        if(!empty($ExcelValidator->get_errors())) {
            $error = implode(',', head(array_values($ExcelValidator->get_errors()['rows'])));
        }

        $this->assertTrue($error == Errors::DISCOUNT_APPLICATION_ERROR);

    }

    /**
     * Testing that valid product id passes.
     *
     * I/P - Valid shopify product id
     * O/P - Test case will assert Empty if the product id passed is valid.
     */
    public function testCorrectActivityShouldPass(){

        $data = array(TestCaseData::DATA);

        $ExcelValidator = new ExcelValidator(TestCaseData::Generate_Raw_Excel($data));
        $ExcelValidator->ValidateHigherEducationData($ExcelValidator->FileFormattedData[0]);

        $this->assertEmpty($ExcelValidator->get_errors());

    }

}
