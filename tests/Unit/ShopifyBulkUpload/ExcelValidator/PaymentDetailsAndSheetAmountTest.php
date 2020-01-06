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

    /** Test case for checking the missing cheque details results in an error.
     *
     * I/P - Missing cheque details for payment having mode as Cheque.
     * O/P - Test case should assert True if the error returned by the ExcelValidator matches the expected error.
     */
    public function testMissingChequeDetailsShouldFail(){

        $error = "";
        $data = TestCaseData::DATA;
        $data['chequedd_no_1'] = "";
        $excel_data = array($data);

        $ExcelValidator = new ExcelValidator($this->generate_raw_excel($excel_data));
        $ExcelValidator->ValidatePaymentDetails($ExcelValidator->FileFormattedData[0]);
        $index = array_search($data['mode_of_payment_1'],array_column($ExcelValidator->FileFormattedData[0]['payments'],'mode_of_payment'));

        if(!empty($ExcelValidator->get_errors())) {
            $error = implode(',', head(array_values($ExcelValidator->get_errors()['rows'])));
        }

        $this->assertTrue($error == sprintf(Errors::CHEQUE_DD_DETAILS_ERROR,$index + 1));

    }

    /** Test case for checking the already used cheque details results in an error.
     *
     * I/P - Already used cheque details for payment having mode as Cheque.
     * O/P - Test case should assert True if the error returned by the ExcelValidator matches the expected error.
     */
    public function testAlreadyUsedChequeDetailsShouldFail(){

        $error = "";
        $data = TestCaseData::DATA;
        $data['date_of_enrollment'] = "02/01/2020";
        $excel_data = array($data);

        $ExcelValidator = new ExcelValidator($this->generate_raw_excel($excel_data));
        $ExcelValidator->ValidatePaymentDetails($ExcelValidator->FileFormattedData[0]);
        $index = array_search($data['mode_of_payment_1'],array_column($ExcelValidator->FileFormattedData[0]['payments'],'mode_of_payment'));

        if(!empty($ExcelValidator->get_errors())) {
            $error = implode(',', head(array_values($ExcelValidator->get_errors()['rows'])));
        }

        $this->assertTrue($error == sprintf(Errors::CHEQUE_DETAILS_USED_ERROR,$index + 1));
    }

    /** Test case for checking that cheque details for cash payment results in an error.
     *
     * I/P - Cheque details for payment having mode as Cash.
     * O/P - Test case should assert True if the error returned by the ExcelValidator matches the expected error.
     */
    public function testIncorrectDetailsForCashPaymentShouldFail(){

        $error = "";
        $data = TestCaseData::DATA;
        $data['mode_of_payment'] = "Cash";
        $data['chequedd_no'] = 34354511;
        $data['txn_reference_number_only_in_case_of_paytm_or_online'] = 345432345343;
        $data['chequedd_no_1'] = 34567;
        $excel_data = array($data);

        $ExcelValidator = new ExcelValidator($this->generate_raw_excel($excel_data));
        $ExcelValidator->ValidatePaymentDetails($ExcelValidator->FileFormattedData[0]);
        $index = array_search($data['mode_of_payment'],array_column($ExcelValidator->FileFormattedData[0]['payments'],'mode_of_payment'));

        if(!empty($ExcelValidator->get_errors())) {
            $error = implode(',', head(array_values($ExcelValidator->get_errors()['rows'])));
        }
        $this->assertTrue($error == sprintf(Errors::CASH_PAYMENT_ERROR, $index + 1));
    }

    /** Test case for checking the missing reference number for NEFT mode results in an error.
     *
     * I/P - Missing reference number for payment having mode as NEFT.
     * O/P - Test case should assert True if the error returned by the ExcelValidator matches the expected error.
     */
    public function testEmptyRefNoForOnlineShouldFail(){

        $error = "";
        $data = TestCaseData::DATA;
        $data['mode_of_payment'] = "NEFT";
        $data['txn_reference_number_only_in_case_of_paytm_or_online'] = "";
        $data['chequedd_no_1'] = 4564322;
        $excel_data = array($data);

        $ExcelValidator = new ExcelValidator($this->generate_raw_excel($excel_data));
        $ExcelValidator->ValidatePaymentDetails($ExcelValidator->FileFormattedData[0]);
        $index = array_search($data['mode_of_payment'],array_column($ExcelValidator->FileFormattedData[0]['payments'],'mode_of_payment'));

        if(!empty($ExcelValidator->get_errors())) {
            $error = implode(',', head(array_values($ExcelValidator->get_errors()['rows'])));
        }

        $this->assertTrue($error == sprintf(Errors::ONLINE_PAYMENT_ERROR,$index + 1));
    }

    /** Test case for checking that any payment having mode as Online results in an error.
     *
     * I/P - Payment having mode as Online.
     * O/P - Test case should assert True if the error returned by the ExcelValidator matches the expected error.
     */
    public function testOnlineNotSupported(){

        $error = "";
        $data = TestCaseData::DATA;
        $data['mode_of_payment'] = "Online";
        $data['chequedd_no_1'] = 45678;
        $excel_data = array($data);

        $ExcelValidator = new ExcelValidator($this->generate_raw_excel($excel_data));
        $ExcelValidator->ValidatePaymentDetails($ExcelValidator->FileFormattedData[0]);
        $index = array_search($data['mode_of_payment'],array_column($ExcelValidator->FileFormattedData[0]['payments'],'mode_of_payment'));

        if(!empty($ExcelValidator->get_errors())) {
            $error = implode(',', head(array_values($ExcelValidator->get_errors()['rows'])));
        }

        $this->assertTrue($error == sprintf(Errors::ONLINE_NOT_SUPPORTED_ERROR,$index + 1));
    }

    /** Test case for checking that invalid payment mode results in an error.
     *
     * I/P - Invalid mode for a payment.
     * O/P - Test case should assert True if the error returned by the ExcelValidator matches the expected error.
     */
    public function testInvalidModeShouldFail(){

        $error = "";
        $data = TestCaseData::DATA;
        $data['mode_of_payment_1'] = "razorpay";
        $mode = $data['mode_of_payment_1'];
        $excel_data = array($data);

        $ExcelValidator = new ExcelValidator($this->generate_raw_excel($excel_data));
        $ExcelValidator->ValidatePaymentDetails($ExcelValidator->FileFormattedData[0]);
        $index = array_search($data['mode_of_payment_1'],array_column($ExcelValidator->FileFormattedData[0]['payments'],'mode_of_payment'));

        if(!empty($ExcelValidator->get_errors())) {
            $error = implode(',', head(array_values($ExcelValidator->get_errors()['rows'])));
        }

        $this->assertTrue($error == sprintf(Errors::INVALID_MODE_ERROR,$mode,$index + 1));
    }

    /** Test case for checking that empty expected amount or expected date for unrecorded payments results in an error.
     *
     * I/P - Empty amount or expected date for unrecorded payments.
     * O/P - Test case should assert True if the error returned by the ExcelValidator matches the expected error.
     */
    public function testExpectedDateAmountEmptyShouldFail(){

        $error = "";
        $data = TestCaseData::DATA;
        $data['mode_of_payment_1'] = "";
        $data['chequedd_date_1'] = "";
        $data['amount_1'] = 3720;
        $excel_data = array($data);

        $ExcelValidator = new ExcelValidator($this->generate_raw_excel($excel_data));
        $ExcelValidator->ValidatePaymentDetails($ExcelValidator->FileFormattedData[0]);
        $index = array_search($data['mode_of_payment_1'],array_column($ExcelValidator->FileFormattedData[0]['payments'],'mode_of_payment'));

        if(!empty($ExcelValidator->get_errors())) {
            $error = implode(',', head(array_values($ExcelValidator->get_errors()['rows'])));
        }

        $this->assertTrue($error == sprintf(Errors::EXPECTED_DATE_AMOUNT_ERROR,$index + 1));

    }

    /** Test case for checking that cheque/dd/online details entered without payment mode results in an error.
     *
     * I/P - cheque/dd/online details entered without payment mode.
     * O/P - Test case should assert True if the error returned by the ExcelValidator matches the expected error.
     */
    public function testEmptyModePaymentDetailsShouldFail(){

        $error = "";
        $data = TestCaseData::DATA;
        $data['mode_of_payment_1'] = "";
        $data['chequedd_date_1'] = "15/01/2020";
        $excel_data = array($data);

        $ExcelValidator = new ExcelValidator($this->generate_raw_excel($excel_data));
        $ExcelValidator->ValidatePaymentDetails($ExcelValidator->FileFormattedData[0]);

        if(!empty($ExcelValidator->get_errors())) {
            $error = implode(',', head(array_values($ExcelValidator->get_errors()['rows'])));
        }

        $this->assertTrue($error == Errors::FUTURE_PAYMENT_CHEQUE_DETAILS_ERROR);
    }

    /** Test case for checking that back date for unrecorded payments results in an error.
     *
     * I/P - Back date for unrecorded payments.
     * O/P - Test case should assert True if the error returned by the ExcelValidator matches the expected error.
     */
    public function testFutureDateForFutureInstallmentsShouldFail(){

        $error = "";
        $data = TestCaseData::DATA;
        $data['mode_of_payment_1'] = "";
        $data['chequedd_date_1'] = "01/01/2020";
        $excel_data = array($data);

        $ExcelValidator = new ExcelValidator($this->generate_raw_excel($excel_data));
        $ExcelValidator->ValidatePaymentDetails($ExcelValidator->FileFormattedData[0]);
        $index = array_search($data['mode_of_payment_1'],array_column($ExcelValidator->FileFormattedData[0]['payments'],'mode_of_payment'));

        if(!empty($ExcelValidator->get_errors())) {
            $error = implode(',', head(array_values($ExcelValidator->get_errors()['rows'])));
        }

        $this->assertTrue($error == sprintf(Errors::FUTURE_INSTALLMENT_DATE_ERROR, $index + 1));
    }

    /** Test case for checking that incorrect total installment amount results in an error.
     *
     * I/P - Incorrect total installment amount.
     * O/P - Test case should assert True if the error returned by the ExcelValidator matches the expected error.
     */
    public function testTotalInstallmentAndFinalFeeMismatchShouldFail(){

        $error = "";
        $data = TestCaseData::DATA;
        $data['amount_1'] = "";
        $excel_data = array($data);

        $ExcelValidator = new ExcelValidator($this->generate_raw_excel($excel_data));
        $ExcelValidator->ValidatePaymentDetails($ExcelValidator->FileFormattedData[0]);

        if(!empty($ExcelValidator->get_errors())) {
            $error = implode(',', head(array_values($ExcelValidator->get_errors()['rows'])));
        }

        $this->assertTrue($error == Errors::ORDER_AMOUNT_TOTAL_ERROR);
    }

    /** Test case for checking that mismatch in expected and recorded cash amount results in an error.
     *
     * I/P - mismatch in expected and recorded cash amount.
     * O/P - Test case should assert True if the error returned by the ExcelValidator matches the expected error.
     */
    public function testCashAmountMismatch(){

        $error = "";
        $data = TestCaseData::DATA;
        $data['date_of_enrollment'] = "04/01/2020";
        $excel_data = array($data);

        $CustomData = ['cash-total' => 0,
            'cheque-total' => 3720,
            'online-total' => 0];

        $ExcelValidator = $ExcelValidator = new ExcelValidator($this->generate_raw_excel($excel_data),$CustomData);
        $ExcelValidator->ValidateAmount();

        if(!empty($ExcelValidator->get_errors())) {
            $error = head($ExcelValidator->get_errors()['sheet']);
        }
        $this->assertTrue($error == sprintf(Errors::CASH_TOTAL_MISMATCH, $CustomData['cash-total'], $data['amount']));

    }

    /** Test case for checking that mismatch in expected and recorded cheque amount results in an error..
     *
     * I/P - Mismatch in expected and recorded cheque amount.
     * O/P - Test case should assert True if the error returned by the ExcelValidator matches the expected error.
     */
    public function testChequeAmountMismatch(){

        $error = "";
        $data = TestCaseData::DATA;
        $data['date_of_enrollment'] = "04/01/2020";
        $excel_data = array($data);

        $CustomData = ['cash-total' => 60000,
            'cheque-total' => 0,
            'online-total' => 0];

        $ExcelValidator = $ExcelValidator = new ExcelValidator($this->generate_raw_excel($excel_data),$CustomData);
        $ExcelValidator->ValidateAmount();

        if(!empty($ExcelValidator->get_errors())) {
            $error = head($ExcelValidator->get_errors()['sheet']);
        }

        $this->assertTrue($error == sprintf(Errors::CHEQUE_TOTAL_MISMATCH, $CustomData['cheque-total'], $data['amount_1']));

    }

    /** Test case for checking that mismatch in expected and recorded online amount results in an error..
     *
     * I/P - Mismatch in expected and recorded online amount.
     * O/P - Test case should assert True if the error returned by the ExcelValidator matches the expected error.
     */
    public function testOnlineAmountMismatch(){

        $error = "";
        $data = TestCaseData::DATA;
        $data['date_of_enrollment'] = "04/01/2020";
        $data['mode_of_payment'] = "Online";
        $excel_data = array($data);

        $CustomData = ['cash-total' => 0,
            'cheque-total' => 3720,
            'online-total' => 0];

        $ExcelValidator = $ExcelValidator = new ExcelValidator($this->generate_raw_excel($excel_data),$CustomData);
        $ExcelValidator->ValidateAmount();

        if(!empty($ExcelValidator->get_errors())) {
            $error = head($ExcelValidator->get_errors()['sheet']);
        }

        $this->assertTrue($error == sprintf(Errors::ONLINE_TOTAL_MISMATCH, $CustomData['online-total'], $data['amount']));

    }

    public function testEmptySheetForAmount(){

        $data = [];
        $excel_data = array($data);

        $CustomData = ['cash-total' => 0,
            'cheque-total' => 3720,
            'online-total' => 0];

        $ExcelValidator = $ExcelValidator = new ExcelValidator($this->generate_raw_excel($excel_data),$CustomData);

        $this->assertEmpty($ExcelValidator->ValidateAmount());
    }

    public function testPreviousAmount()
    {
        $error = "";
        $data = TestCaseData::DATA;
        $data['amount_1'] = 3000;
        $data['mode_of_payment_2'] = "NEFT";
        $data['amount_2'] = 720;

        $excel_data = array($data);

        $CustomData = ['cash-total' => 0,
            'cheque-total' => 0,
            'online-total' => 0];

        $ExcelValidator = $ExcelValidator = new ExcelValidator($this->generate_raw_excel($excel_data),$CustomData);
        $ExcelValidator->ValidateAmount();

        if(!empty($ExcelValidator->get_errors())) {
            $error = head($ExcelValidator->get_errors()['sheet']);
        }

        $this->assertEmpty($error);
    }





























}
