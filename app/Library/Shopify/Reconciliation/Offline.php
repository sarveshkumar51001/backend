<?php

namespace App\Library\Shopify\Reconciliation;

use App\Models\ShopifyExcelUpload;
use App\Models\Transaction;
use App\Library\Reconcile\Source\ISource;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Arr;

/**
 * Reconcile offline class for bank related payment reconcile
 */
class Offline extends Base
{
    /**
     * @param array $row
     *
     * @return array
     * @throws \Exception
     */
    public function CheckAndMarkSettled(array $row) {
        $data = [];

        /* @var ISource $sourceClass */
        $sourceClass = $this->File->GetSourceClass();

        // Create an array for the row
        foreach ($sourceClass::GetColumns() as $column) {
            $data[$column] = $row[$column];
        }

        /* @var ISource $Source */
        $Source = new $sourceClass($data);

        // Normalise to number fields
        $modeNumber = $Source->GetModeNumber();

        $this->metadata[self::FILE_AMOUNT] += $Source->GetModeAmount();

        $accountNumber = '';
        $mode = 0;
        if($Source->IsCheque()) {
            $mode = ShopifyExcelUpload::$modesTitle[ShopifyExcelUpload::MODE_CHEQUE];
            $accountNumber = $Source->GetAccountNumber();
        } else if($Source->IsDD()) {
            $mode = ShopifyExcelUpload::$modesTitle[ShopifyExcelUpload::MODE_DD];
            $accountNumber = $Source->GetMICRCode();
        }

        $Orders = ShopifyExcelUpload::where('payments.chequedd_no', $modeNumber)
            ->where('payments.drawee_account_number', $accountNumber)
            ->where('payments.amount', $Source->GetModeAmount())
            ->where('payments.chequedd_date', $Source->GetModeDate())
            ->where('payments.mode_of_payment', $mode)
            ->get();

        // Start Matching
        if(count($Orders)) {

            // If more than one Orders found, raise an error
            if($Orders->count() > 1) {
                $modeIDList = [];
                foreach ($Orders as $Order) {
                    $OrderIDList[] = $Order->getOrder();
                }

                $this->metadata[self::FAILED_ROWS_COUNT] += 1;
                $this->metadata[self::FAILED_AMOUNT] += $Source->GetModeAmount();
                $this->errors[] = $data = array_merge($data, ['error' => 'Multiple payment modes ' . implode(',', $modeIDList). 'found for same cheque/reference number',
                    'reco_status' => 400]);

                return $data;
            }

            // We are sure now, we loaded the mode to handle
            $Order = $Orders->first();

            $payment_match_count = 0;
            $matched_payment = [];
            foreach ($Order['payments'] as $index => $payment) {
                if($payment['chequedd_no'] == $modeNumber && $payment['drawee_account_number'] == $accountNumber
                    && $payment['amount'] == $Source->GetModeAmount() && $payment['chequedd_date'] == $Source->GetModeDate()
                    && $payment['mode_of_payment'] == $mode) {
                    $payment_match_count++;
                    if ($payment_match_count > 1)
                        break;
                    $Payment = new Payment($payment, $index);
                }
            }

            if($payment_match_count == 0) {
                $this->errors[] = $data = array_merge($data, ['error' => 'No Payment Match - Reconcile File(' . $Source->GetStudentID() . ')',
                    'reco_status' => 400]);
                return $data;
            } elseif ($payment_match_count > 1) {
                $this->errors[] = $data = array_merge($data, ['error' => 'Multiple Payment Match - Reconcile File(' . $Source->GetStudentID() . ')',
                    'reco_status' => 400]);
                return $data;
            }

            if(!$Payment->isProcessed()) {
                $this->errors[] = $data = array_merge($data, ['error' => 'Transaction is not yet processed',
                    'reco_status' => 400]);
            }

	        // Make sure the mode amount matches against the stored amount
	        if($Payment->getAmount() != $Source->GetModeAmount()) {
		        $this->metadata[self::FAILED_ROWS_COUNT] += 1;
                $this->metadata[self::FAILED_AMOUNT] += $Source->GetModeAmount();
		        $this->errors[] = $data = array_merge($data, ['error' => 'Amount mismatch - System Amount(' . $Payment->getAmount() . ') Reconcile File(' . $Source->GetModeAmount() . ')',
		                                                      'reco_status' => 400]);

		        return $data;
	        }

	        // Make sure either returned or paid is found
	        if(!$Source->IsReturned() && !$Source->IsPaid()) {
		        $this->metadata[self::FAILED_ROWS_COUNT] += 1;
		        $this->metadata[self::FAILED_AMOUNT] += $Source->GetModeAmount();
		        $this->errors[] = $data = array_merge($data, ['error' => 'Invalid status ['.$Source->GetStatus().'] found from bank transaction', 'reco_status' => 400]);

		        return $data;
	        }

	        if($Payment->isReconciled()) {
		        $this->metadata[self::ALREADY_SETTLED_ROWS_COUNT] += 1;
		        $this->metadata[self::ALREADY_SETTLED_AMOUNT] += $Source->GetModeAmount();
		        $this->errors[] = $data = array_merge($data, ['error' => 'Transaction is already settled with status [' . $Payment->getRecoStatus() .']', 'reco_status' => 409]);

		        return $data;
	        }

            // By now all is good. so we can process further

            $data['reco_status'] = 200; // All ok
            $this->metadata[self::SETTLED_ROWS_COUNT] += 1;
            $this->metadata[self::FILE_SETTLEABLE_AMOUNT] += $Source->GetModeAmount();

            $loggedInUser = (\Auth::user()->id ?? 0);

            if(!$this->IsSandbox()) {
                if($Source->IsReturned()) {
                    $updates = [
                        ShopifyExcelUpload::PaymentSettlementStatus => ShopifyExcelUpload::PAYMENT_SETTLEMENT_STATUS_RETURNED,
                        ShopifyExcelUpload::PaymentSettlementMode => ShopifyExcelUpload::PAYMENT_SETTLEMENT_MODE_BANK,
                        ShopifyExcelUpload::PaymentReturnedDate => $Source->GetReturnedDate(),
                        ShopifyExcelUpload::PaymentReturnedBy => $loggedInUser,
                        ShopifyExcelUpload::PaymentUpdatedAt => time(),
                    ];

                    $this->metadata[self::RETURNED_ROWS_COUNT] += 1;
                } else if($Source->IsPaid()) {
                    $updates = [
                        ShopifyExcelUpload::PaymentSettlementStatus => ShopifyExcelUpload::PAYMENT_SETTLEMENT_STATUS_SETTLED,
                        ShopifyExcelUpload::PaymentSettlementMode => ShopifyExcelUpload::PAYMENT_SETTLEMENT_MODE_BANK,
                        ShopifyExcelUpload::PaymentLiquidationDate => $Source->GetLiquidationDate(),
                        ShopifyExcelUpload::PaymentSettledDate => time(),
                        ShopifyExcelUpload::PaymentSettledBy => $loggedInUser,
                        ShopifyExcelUpload::PaymentUpdatedAt => time(),
                    ];
                } else {
                    $this->metadata[self::FAILED_ROWS_COUNT] += 1;
                    $this->metadata[self::FAILED_AMOUNT] += $Source->GetModeAmount();
                    // Failed to reco
                    $this->errors[] = $data = array_merge($data, ['error' => 'Invalid status ['.$Source->GetStatus().'] found from bank transaction', 'fabs_reco_status' => 400]);

                    return $data;
                }

                $updates[ShopifyExcelUpload::PaymentDepositDate] = $Source->GetDepositDate();

                $column_updates = [];
                foreach ($updates as $column => $value) {
                    $key_name = sprintf("payments.%s.%s.%s", $Payment->getIndex(), Payment::RECO, $column);
                    $column_updates[$key_name] = $value;
                }
                $Order->update($column_updates);

                // Update the settled count by 1
                $this->success[] = $data;
            }
        } else {
            $data['reco_status'] = 404; // Not found
            $data['error'] = 'Transaction not found'; // Not found
            $this->metadata[self::NOT_FOUND_ROWS_COUNT] += 1;
            $this->metadata[self::NOT_FOUND_AMOUNT] += $Source->GetModeAmount();
        }

        return $data;
    }
}
