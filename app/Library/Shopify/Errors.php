<?php

namespace App\Library\Shopify;

class Errors
{

    const EMPTY_FILE_ERROR = "No data was found in the uploaded file";

    const INCORRECT_HEADER_ERROR = "Either few headers are incorrect or wrong sheet uploaded, to resolve download the latest sample format";

    const DUPLICATE_ROW_ERROR = "This order has already been processed";

    const CONTACT_DETAILS_ERROR = "Either Email or Mobile Number is mandatory.";

    const LOCATION_ERROR = "No location exists for Delivery Institution and Branch";

    const INTERNAL_TYPE_ERROR = "The order type should be internal for schools under Apeejay Education Society and delivery institution should be Apeejay.";

    const EXTERNAL_TYPE_ERROR = "The order type should be external for schools outside Apeejay and delivery institution should be other than Apeejay.";

    const ACTIVITY_ID_ERROR = "Activity ID is either incorrect or not available.";

    const DUPLICATE_ACTIVITY_ERROR = "More than one product exists with Activity ID %s";

    const ACTIVITY_FEE_ERROR = "Activity Fee entered is incorrect.";

    const OUT_OF_STOCK_ERROR = "Product is out of stock.";

    const FINAL_FEE_ERROR = "Final Fee is not equal to the activity fee.";

    const DISCOUNT_APPLICATION_ERROR = "After applying discount and Final Fee amount does not match.";

    const FIELD_UPDATED_ERROR = "Only Payment data can be updated for an Order. Field(s) %s has been changed";

    const PROCESSED_INSTALLMENT_ERROR = "Already Processed installments can't be modified. Installment %s have been modified";

    const CASH_TOTAL_MISMATCH = "Cash total mismatch, Entered total %s, Calculated total %s";

    const CHEQUE_TOTAL_MISMATCH = "Cheque total mismatch, Entered total %s, Calculated total %s";

    const ONLINE_TOTAL_MISMATCH = "Online total mismatch, Entered total %s, Calculated total %s";

    const EMPTY_AMOUNT_ERROR = "Amount is required for payment - %s";

    const CHEQUE_DD_DETAILS_ERROR = "Cheque/DD Details are mandatory for payment - %s, which is having payment mode as Cheque/DD.";

    const CHEQUE_DETAILS_USED_ERROR = "Cheque/DD Details already used before for payment - %s.";

    const ONLINE_PAYMENT_ERROR = "Transaction Reference No. is mandatory for payment - %s, which is an online payment.";

    const CASH_PAYMENT_ERROR = "Cheque/DD/Online payment details are not applicable for payment - %s, as it is a cash payment.";

    const INVALID_MODE_ERROR = "Invalid mode of payment - %s for payment %s ";

    const EXPECTED_DATE_AMOUNT_ERROR = "Expected Amount and Expected date of collection required for payment - %s.";

    const FUTURE_PAYMENT_CHEQUE_DETAILS_ERROR = "Future Installments with no payment mode cannot have Cheque/DD/Online details";

    const FUTURE_INSTALLMENT_DATE_ERROR = "Payment date should be in future for future payment - %s.";

    const ORDER_AMOUNT_TOTAL_ERROR = "Total Installment Amount and Final Fee Amount does not match";

    const OUTSIDE_APEEJAY_ERROR = "The order type should be external for institutes outside Apeejay.";

    const ONLINE_NOT_SUPPORTED_ERROR = "Payment %s - Online Payment mode is currently not supported.";

    const INSTITUTE_ERROR = "The order type should be internal for institutes under Apeejay Education Society and delivery institution should be Apeejay.";

    const INSTITUTE_CLASS_ERROR = "Incorrect class given for higher education institutes.";

    const INSTITUTE_SECTION_ERROR = "Incorrect section given for higher education institutes.";

    const SCHOOL_CLASS_ERROR = "Higher Education classes are not valid for school entries.";

    CONST SCHOOL_SECTION_ERROR = "Higher Education sections are not valid for school entries.";

    const REYNOTT_CLASS_ERROR = "Class entered for Reynott academy is incorrect.";

    const REYNOTT_SECTION_ERROR = "Section entered for Reynott academy is incorrect.";

    const REYNOTT_INTERDEPENDENCE_ERROR = "For classes Dropper and Crash, section can only be Reynott.";

    const INCORRECT_APEEJAY_ORDER = "The order type should be internal for Apeejay schools.";

    const INCORRECT_NON_APEEJAY_ORDER = "The order type should be external for schools outside Apeejay.";

    const SHEET_ERRORS = 'There are errors in sheets due to which collection cannot be calculated correctly. Please correct below errors and try again.';

    const HAYDEN_REYNOTT_CLASS_ERROR = "Class entered for H&R is incorrect.";

    const HAYDEN_REYNOTT_SECTION_ERROR = "Section entered for Hayden & Reynott is incorrect. It can only be H&R";

}
