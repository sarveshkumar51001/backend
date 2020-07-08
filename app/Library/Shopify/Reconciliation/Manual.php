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

        // Get Transaction Id
        $transaction_id = $Source->GetTransactionID();

        $Order = ShopifyExcelUpload::where('payments.transaction_id', (int) $transaction_id);

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
            $this->metadata[self::SETTLED_TRANSACTIONS_COUNT] += 1;
        }
        else {
            $updates[ShopifyExcelUpload::PaymentSettlementStatus] =  ShopifyExcelUpload::PAYMENT_SETTLEMENT_STATUS_RETURNED;
            $this->metadata[self::RETURNED_ROWS_COUNT] += 1;
        }

        $column_updates = [];
        foreach ($Order->first()->toArray()['payments'] as $index => $payment)
        {
            if(isset($payment['transaction_id']) && $payment['transaction_id'] == $transaction_id) {

                foreach ($updates as $column => $value) {
                    $key_name = sprintf("payments.%s.%s.%s", $index,Payment::RECO, $column);
                    $column_updates[$key_name] = $value;
                }
                $Order->update($column_updates);
            }
        }
        //return $Order;
    }
}
