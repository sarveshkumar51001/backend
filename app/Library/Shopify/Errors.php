<?php

namespace App\Library\Shopify;

class Errors {
	

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

	const FINAL_FEE_ERROR = "Final Fee  is not equal to the activity fee.";

	const DISCOUNT_APPLICATION_ERROR = "After applying discount and Final Fee amount does not match.";

	const FIELD_UPDATED_ERROR = "Only Payment data can be updated for an Order. Field(s) %s has been changed";

	const PROCESSED_INSTALLMENT_ERROR = "Already Processed installments can't be modified. Installment %s have been modified";

	const CASH_TOTAL_MISMATCH = "Cash total mismatch, Entered total %s, Calculated total %s";

	const CHEQUE_TOTAL_MISMATCH = "Cheque total mismatch, Entered total %s, Calculated total %s";

	const ONLINE_TOTAL_MISMATCH = "Online total mismatch, Entered total %s, Calculated total %s";

	const EMPTY_AMOUNT_ERROR = "Amount is required for any payment";

	const CHEQUE_DD_DETAILS_ERROR = "Cheque/DD Details are mandatory for transactions having payment mode as Cheque/DD.";

	const CHEQUE_DETAILS_USED_ERROR = "Cheque/DD Details already used before.";

	const ONLINE_PAYMENT_ERROR = "Transaction Reference No. is mandatory in case of Online Payment.";

	const CASH_PAYMENT_ERROR = "For Cash payments, Cheque/DD/Online payment details are not applicable.";

	const INVALID_MODE_ERROR = "Payment %s - Invalid Payment Mode - %s";

	const EXPECTED_DATE_AMOUNT_ERROR = "Expected Amount and Expected date of collection required for every installment of this order.";

	const FUTURE_PAYMENT_CHEQUE_DETAILS_ERROR = "Future Installments with no payment mode cannot have Cheque/DD/Online details";

	const FUTURE_INSTALLMENT_DATE_ERROR = "Payment date should be in future for future installments";

	const ORDER_AMOUNT_TOTAL_ERROR = "Total Installment Amount and Final Fee Amount does not match";

}