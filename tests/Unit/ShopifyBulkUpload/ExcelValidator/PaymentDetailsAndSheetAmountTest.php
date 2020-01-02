<?php

namespace Tests\Unit\ShopifyBulkUpload;

use App\Library\Shopify\Errors;
use App\Library\Shopify\Excel;
use App\Library\Shopify\ExcelValidator;
use App\Models\ShopifyExcelUpload;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;
use Tests\TestCaseData;


class PaymentDetailsAndSheetAmountTest extends TestCase
{

    /**
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
    //D
    public function testMissingChequeDetailsShouldFail(){

        $data = TestCaseData::DATA;
        $data['chequedd_no'] = "";
        $excel_data = array($data);

        $ExcelValidator = new ExcelValidator($this->generate_raw_excel($excel_data));
        $ExcelValidator->ValidatePaymentDetails($ExcelValidator->FileFormattedData[0]);
        $index = array_search('Cheque',array_column($ExcelValidator->FileFormattedData[0]['payments'],'mode_of_payment'));
        $error = implode(',',head(array_values($ExcelValidator->get_errors()['rows'])));

        $this->assertTrue($error == sprintf(Errors::CHEQUE_DD_DETAILS_ERROR,$index + 1));

    }

    //TD
    public function testAlreadyUsedChequeDetailsShouldFail(){

        $data = TestCaseData::DATA;
        $excel_data = array($data);

        $ExcelValidator = new ExcelValidator($this->generate_raw_excel($excel_data));
        $ExcelValidator->ValidatePaymentDetails($ExcelValidator->FileFormattedData[0]);
        $error = implode(',',head(array_values($ExcelValidator->get_errors()['rows'])));

    }


    public function testIncorrectDetailsForCashPaymentShouldFail(){

        $data = TestCaseData::DATA;
        $data['chequedd_no'] = 343545;
        $excel_data = array($data);

        $ExcelValidator = new ExcelValidator($this->generate_raw_excel($excel_data));
        $ExcelValidator->ValidatePaymentDetails($ExcelValidator->FileFormattedData[0]);
        $index = array_search('Cash',array_column($ExcelValidator->FileFormattedData[0]['payments'],'mode_of_payment'));
        $error = implode(',',head(array_values($ExcelValidator->get_errors()['rows'])));

        $this->assertTrue($error == sprintf(Errors::CHEQUE_DD_DETAILS_ERROR,$index + 1));
    }

    public function testEmptyRefNoForOnlineShouldFail(){

        $data = TestCaseData::DATA;
        $excel_data = array($data);

        $ExcelValidator = new ExcelValidator($this->generate_raw_excel($excel_data));
        $ExcelValidator->ValidatePaymentDetails($ExcelValidator->FileFormattedData[0]);
        $error = implode(',',head(array_values($ExcelValidator->get_errors()['rows'])));
    }

    public function testOnlineNotSupported(){

        $data = TestCaseData::DATA;
        $excel_data = array($data);

        $ExcelValidator = new ExcelValidator($this->generate_raw_excel($excel_data));
        $ExcelValidator->ValidatePaymentDetails($ExcelValidator->FileFormattedData[0]);
        $error = implode(',',head(array_values($ExcelValidator->get_errors()['rows'])));
    }

    public function testInvalidModeShouldFail(){

        $data = TestCaseData::DATA;
        $data['mode_of_payment_1'] = "Razorpay";
        $excel_data = array($data);

        $ExcelValidator = new ExcelValidator($this->generate_raw_excel($excel_data));
        $ExcelValidator->ValidatePaymentDetails($ExcelValidator->FileFormattedData[0]);
        $error = implode(',',head(array_values($ExcelValidator->get_errors()['rows'])));
    }

    public function testExpectedDateAmountEmptyShouldFail(){

        $data = TestCaseData::DATA;
        $excel_data = array($data);

        $ExcelValidator = new ExcelValidator($this->generate_raw_excel($excel_data));
        $ExcelValidator->ValidatePaymentDetails($ExcelValidator->FileFormattedData[0]);
        $error = implode(',',head(array_values($ExcelValidator->get_errors()['rows'])));
    }

    public function testEmptyModePaymentDetailsShouldFail(){
        $data = TestCaseData::DATA;
        $excel_data = array($data);

        $ExcelValidator = new ExcelValidator($this->generate_raw_excel($excel_data));
        $ExcelValidator->ValidatePaymentDetails($ExcelValidator->FileFormattedData[0]);
        $error = implode(',',head(array_values($ExcelValidator->get_errors()['rows'])));
    }

    public function testFutureDateForFutureInstallmentsShouldFail(){
        $data = TestCaseData::DATA;
        $excel_data = array($data);

        $ExcelValidator = new ExcelValidator($this->generate_raw_excel($excel_data));
        $ExcelValidator->ValidatePaymentDetails($ExcelValidator->FileFormattedData[0]);
        $error = implode(',',head(array_values($ExcelValidator->get_errors()['rows'])));

    }

    public function testTotalInstallmentAndFinalFeeMismatchShouldFail(){

        $data = TestCaseData::DATA;
        $excel_data = array($data);

        $ExcelValidator = new ExcelValidator($this->generate_raw_excel($excel_data));
        $ExcelValidator->ValidatePaymentDetails($ExcelValidator->FileFormattedData[0]);
        $error = implode(',',head(array_values($ExcelValidator->get_errors()['rows'])));
    }






























}
