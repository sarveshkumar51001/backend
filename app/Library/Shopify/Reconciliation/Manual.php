<?php

namespace App\Library\Shopify\Reconciliation;

use App\Models\ShopifyExcelUpload;
use App\Models\Transaction;
use App\Library\Reconcile\Source\ISource;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Arr;
use Carbon\Carbon;
/**
 * Reconcile offline class for bank related payment reconcile
 */
class Manual extends Base
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

        // Get Order by transaction id
        $Order = ShopifyExcelUpload::where('payments.transaction_id', (int) $Source->GetTransactionID());
        if(!is_null($Order->first()) && count($Order->first()->toArray())) {

            $payment_match_count = 0;
            foreach ($Order->first()->toArray()['payments'] as $index => $payment)
            {
                if( isset($payment['transaction_id'])
                    && $payment['transaction_id'] == $Source->GetTransactionID()
                ) {
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
            }

            else if ($payment_match_count > 1) {
                $this->errors[] = $data = array_merge($data, ['error' => 'Multiple Payment Match - Reconcile File(' . $Source->GetStudentID() . ')',
                    'reco_status' => 400]);
                return $data;
            }

            if( !in_array( $Source->GetReconciliationStatus(), array( ShopifyExcelUpload::PAYMENT_RECONCILIATION_STATUS[0], ShopifyExcelUpload::PAYMENT_RECONCILIATION_STATUS[0]))) {
                $this->errors[] = $data = array_merge($data, ['error' => 'Status does not match. Transaction ID : [' . $Payment->getTransactionID() .']',
                    'reco_status' => 400]);
            }

            if(!$Payment->isProcessed()) {
                $this->errors[] = $data = array_merge($data, ['error' => 'Transaction is not yet processed. Transaction ID : [' . $Payment->getTransactionID() .']',
                    'reco_status' => 400]);
            }

            if((Carbon::parse($Source->GetModeDate())->timestamp) > (Carbon::now()->timestamp)) {
                $this->errors[] = $data = array_merge($data, ['error' => 'Cheque date is invalid : [' . $Payment->getTransactionID() .']',
                    'reco_status' => 400]);
            }

            if($Payment->getAmount() != $Source->GetModeAmount()) {
                $this->metadata[self::FAILED_ROWS_COUNT] += 1;
                $this->metadata[self::FAILED_AMOUNT] += $Source->GetModeAmount();
                $this->errors[] = $data = array_merge($data, ['error' => 'Amount mismatch - System Amount(' . $Payment->getAmount() . ') Reconcile File(' . $Source->GetModeAmount() . ') .Transaction ID : [' . $Payment->getTransactionID() .']',
                    'reco_status' => 400]);
                return $data;
            }

            if($Payment->isReconciled()) {
                $this->metadata[self::ALREADY_SETTLED_ROWS_COUNT] += 1;
                $this->metadata[self::FAILED_AMOUNT] += $Source->GetModeAmount();
                $this->errors[] = $data = array_merge($data, ['error' => 'Transaction is already settled with status [' . $Payment->getRecoStatus() .'] . Transaction ID : [' . $Payment->getTransactionID() .']', 'reco_status' => 409]);
                return $data;
            }

            $loggedInUser = (\Auth::user()->id ?? 0);

            $updates = [
                ShopifyExcelUpload::PaymentSettlementStatus => ShopifyExcelUpload::PAYMENT_SETTLEMENT_STATUS_RETURNED,
                ShopifyExcelUpload::PaymentSettlementMode => ShopifyExcelUpload::PAYMENT_SETTLEMENT_MODE_MANUAL,
                ShopifyExcelUpload::PaymentSettledDate => time(),
                ShopifyExcelUpload::PaymentSettledBy => $loggedInUser,
                ShopifyExcelUpload::PaymentUpdatedAt => time(),
                ShopifyExcelUpload::PaymentRemarks => $Source->GetTransactionRemark(),
            ];

            if(strtolower($Source->GetReconciliationStatus()) == ShopifyExcelUpload::PAYMENT_SETTLEMENT_STATUS_SETTLED) {
                $updates[ShopifyExcelUpload::PaymentSettlementStatus] =  ShopifyExcelUpload::PAYMENT_SETTLEMENT_STATUS_SETTLED;
            }

            if(strtolower($Source->GetReconciliationStatus()) == ShopifyExcelUpload::PAYMENT_SETTLEMENT_STATUS_RETURNED) {
                $updates[ShopifyExcelUpload::PaymentSettlementStatus] =  ShopifyExcelUpload::PAYMENT_SETTLEMENT_STATUS_RETURNED;
                $this->metadata[self::RETURNED_ROWS_COUNT] += 1;
            }

            $column_updates = [];
            foreach ($updates as $column => $value) {
                $key_name = sprintf("payments.%s.%s.%s", $Payment->getIndex(), Payment::RECO, $column);
                $column_updates[$key_name] = $value;
            }

            $Order->update($column_updates);
            $data['reco_status'] = 200;
            $this->metadata[self::SETTLED_ROWS_COUNT] += 1;
            $this->metadata[self::FILE_SETTLEABLE_AMOUNT] += $Source->GetModeAmount();
            $this->success[] = $data;
        }
        else {
            $data['reco_status'] = 404; // Not found
            $data['error'] = 'Transaction not found'; // Not found
            $this->metadata[self::NOT_FOUND_ROWS_COUNT] += 1;
        }

        return $data;
    }
}
