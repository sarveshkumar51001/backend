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
        $data['chequedd_no_1'] = "";
        $excel_data = array($data);

        $ExcelValidator = new ExcelValidator($this->generate_raw_excel($excel_data));
        $ExcelValidator->ValidatePaymentDetails($ExcelValidator->FileFormattedData[0]);
        $index = array_search($data['mode_of_payment_1'],array_column($ExcelValidator->FileFormattedData[0]['payments'],'mode_of_payment'));
        $error = implode(',',head(array_values($ExcelValidator->get_errors()['rows'])));

        $this->assertTrue($error == sprintf(Errors::CHEQUE_DD_DETAILS_ERROR,$index + 1));

    }

    //D
    public function testAlreadyUsedChequeDetailsShouldFail(){

        $data = TestCaseData::DATA;
        $excel_data = array($data);

        $ExcelValidator = new ExcelValidator($this->generate_raw_excel($excel_data));
        $ExcelValidator->ValidatePaymentDetails($ExcelValidator->FileFormattedData[0]);
        $index = array_search($data['mode_of_payment_1'],array_column($ExcelValidator->FileFormattedData[0]['payments'],'mode_of_payment'));
        $error = implode(',',head(array_values($ExcelValidator->get_errors()['rows'])));

        $this->assertTrue($error == sprintf(Errors::CHEQUE_DETAILS_USED_ERROR,$index + 1));
    }

    //D
    public function testIncorrectDetailsForCashPaymentShouldFail(){

        $data = TestCaseData::DATA;
        $data['mode_of_payment'] = "Cash";
        $data['chequedd_no'] = 34354511;
        $data['txn_reference_number_only_in_case_of_paytm_or_online'] = 345432345343;
        $data['chequedd_no_1'] = 34567;
        $excel_data = array($data);

        $ExcelValidator = new ExcelValidator($this->generate_raw_excel($excel_data));
        $ExcelValidator->ValidatePaymentDetails($ExcelValidator->FileFormattedData[0]);
        $index = array_search($data['mode_of_payment'],array_column($ExcelValidator->FileFormattedData[0]['payments'],'mode_of_payment'));
        $error = implode(',',head(array_values($ExcelValidator->get_errors()['rows'])));

        $this->assertTrue($error == sprintf(Errors::CASH_PAYMENT_ERROR, $index + 1));
    }

    //D
    public function testEmptyRefNoForOnlineShouldFail(){

        $data = TestCaseData::DATA;
        $data['mode_of_payment'] = "NEFT";
        $data['txn_reference_number_only_in_case_of_paytm_or_online'] = "";
        $data['chequedd_no_1'] = 4564322;
        $excel_data = array($data);

        $ExcelValidator = new ExcelValidator($this->generate_raw_excel($excel_data));
        $ExcelValidator->ValidatePaymentDetails($ExcelValidator->FileFormattedData[0]);
        $index = array_search($data['mode_of_payment'],array_column($ExcelValidator->FileFormattedData[0]['payments'],'mode_of_payment'));
        $error = implode(',',head(array_values($ExcelValidator->get_errors()['rows'])));

        $this->assertTrue($error == sprintf(Errors::ONLINE_PAYMENT_ERROR,$index + 1));
    }

    //D
    public function testOnlineNotSupported(){

        $data = TestCaseData::DATA;
        $data['mode_of_payment'] = "Online";
        $data['chequedd_no_1'] = 45678;
        $excel_data = array($data);

        $ExcelValidator = new ExcelValidator($this->generate_raw_excel($excel_data));
        $ExcelValidator->ValidatePaymentDetails($ExcelValidator->FileFormattedData[0]);
        $index = array_search($data['mode_of_payment'],array_column($ExcelValidator->FileFormattedData[0]['payments'],'mode_of_payment'));
        $error = implode(',',head(array_values($ExcelValidator->get_errors()['rows'])));

        $this->assertTrue($error == sprintf(Errors::ONLINE_NOT_SUPPORTED_ERROR,$index + 1));
    }

    //D
    public function testInvalidModeShouldFail(){

        $data = TestCaseData::DATA;
        $data['mode_of_payment_1'] = "razorpay";
        $mode = $data['mode_of_payment_1'];
        $excel_data = array($data);

        $ExcelValidator = new ExcelValidator($this->generate_raw_excel($excel_data));
        $ExcelValidator->ValidatePaymentDetails($ExcelValidator->FileFormattedData[0]);
        $index = array_search($data['mode_of_payment_1'],array_column($ExcelValidator->FileFormattedData[0]['payments'],'mode_of_payment'));
        $error = implode(',',head(array_values($ExcelValidator->get_errors()['rows'])));

        $this->assertTrue($error == sprintf(Errors::INVALID_MODE_ERROR,$mode,$index + 1));
    }

    //D
    public function testExpectedDateAmountEmptyShouldFail(){

        $data = TestCaseData::DATA;
        $data['mode_of_payment_1'] = "";
        $data['chequedd_date_1'] = "";
        $data['amount_1'] = 3720;
        $excel_data = array($data);

        $ExcelValidator = new ExcelValidator($this->generate_raw_excel($excel_data));
        $ExcelValidator->ValidatePaymentDetails($ExcelValidator->FileFormattedData[0]);
        $index = array_search($data['mode_of_payment_1'],array_column($ExcelValidator->FileFormattedData[0]['payments'],'mode_of_payment'));
        $error = implode(',',head(array_values($ExcelValidator->get_errors()['rows'])));

        $this->assertTrue($error == sprintf(Errors::EXPECTED_DATE_AMOUNT_ERROR,$index + 1));

    }

    //D
    public function testEmptyModePaymentDetailsShouldFail(){
        $data = TestCaseData::DATA;
        $data['mode_of_payment_1'] = "";
        $data['chequedd_date_1'] = "15/01/2020";
        $excel_data = array($data);

        $ExcelValidator = new ExcelValidator($this->generate_raw_excel($excel_data));
        $ExcelValidator->ValidatePaymentDetails($ExcelValidator->FileFormattedData[0]);
        $error = implode(',',head(array_values($ExcelValidator->get_errors()['rows'])));

        $this->assertTrue($error == Errors::FUTURE_PAYMENT_CHEQUE_DETAILS_ERROR);
    }

    //D
    public function testFutureDateForFutureInstallmentsShouldFail(){
        $data = TestCaseData::DATA;
        $data['mode_of_payment_1'] = "";
        $data['chequedd_date_1'] = "01/01/2020";
        $excel_data = array($data);

        $ExcelValidator = new ExcelValidator($this->generate_raw_excel($excel_data));
        $ExcelValidator->ValidatePaymentDetails($ExcelValidator->FileFormattedData[0]);
        $index = array_search($data['mode_of_payment_1'],array_column($ExcelValidator->FileFormattedData[0]['payments'],'mode_of_payment'));
        $error = implode(',',head(array_values($ExcelValidator->get_errors()['rows'])));

        $this->assertTrue($error == sprintf(Errors::FUTURE_INSTALLMENT_DATE_ERROR, $index + 1));
    }

    //D
    public function testTotalInstallmentAndFinalFeeMismatchShouldFail(){

        $data = TestCaseData::DATA;
        $data['amount_1'] = "";
        $excel_data = array($data);

        $ExcelValidator = new ExcelValidator($this->generate_raw_excel($excel_data));
        $ExcelValidator->ValidatePaymentDetails($ExcelValidator->FileFormattedData[0]);
        $error = implode(',',head(array_values($ExcelValidator->get_errors()['rows'])));

        $this->assertTrue($error == Errors::ORDER_AMOUNT_TOTAL_ERROR);
    }

    //D
    public function testCashAmountMismatch(){

        $data = TestCaseData::DATA;
        $data['date_of_enrollment'] = "03/01/2020";
        $excel_data = array($data);

        $CustomData = ['cash-total' => 0,
            'cheque-total' => 3720,
            'online-total' => 0];

        $ExcelValidator = $ExcelValidator = new ExcelValidator($this->generate_raw_excel($excel_data),$CustomData);
        $ExcelValidator->ValidateAmount();
        $error = head($ExcelValidator->get_errors()['sheet']);

        $this->assertTrue($error == sprintf(Errors::CASH_TOTAL_MISMATCH, $CustomData['cash-total'], $data['amount']));

    }

    //D
    public function testChequeAmountMismatch(){

        $data = TestCaseData::DATA;
        $data['date_of_enrollment'] = "03/01/2020";
        $excel_data = array($data);

        $CustomData = ['cash-total' => 60000,
            'cheque-total' => 0,
            'online-total' => 0];

        $ExcelValidator = $ExcelValidator = new ExcelValidator($this->generate_raw_excel($excel_data),$CustomData);
        $ExcelValidator->ValidateAmount();
        $error = head($ExcelValidator->get_errors()['sheet']);

        $this->assertTrue($error == sprintf(Errors::CHEQUE_TOTAL_MISMATCH, $CustomData['cheque-total'], $data['amount_1']));

    }

    //D
    public function testOnlineAmountMismatch(){

        $data = TestCaseData::DATA;
        $data['date_of_enrollment'] = "03/01/2020";
        $data['mode_of_payment'] = "Online";
        $excel_data = array($data);

        $CustomData = ['cash-total' => 0,
            'cheque-total' => 3720,
            'online-total' => 0];

        $ExcelValidator = $ExcelValidator = new ExcelValidator($this->generate_raw_excel($excel_data),$CustomData);
        $ExcelValidator->ValidateAmount();
        $error = head($ExcelValidator->get_errors()['sheet']);

        $this->assertTrue($error == sprintf(Errors::ONLINE_TOTAL_MISMATCH, $CustomData['online-total'], $data['amount']));



    }































}
