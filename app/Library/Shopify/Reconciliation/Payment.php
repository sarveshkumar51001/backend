<?php
namespace App\Library\Shopify\Reconciliation;

use App\Models\ShopifyExcelUpload;
use Carbon\Carbon;

class Payment {

    private $payment;

    private $index;

    const RECO = 'reconciliation';

    public function __construct(array $payment, int $index = 0)
    {
        $this->payment = $payment;
        $this->index = $index;
    }

    public function isSettled() {
        return $this->getRecoStatus() == ShopifyExcelUpload::PAYMENT_SETTLEMENT_STATUS_SETTLED;
    }

    public function isReturned() {
        return $this->getRecoStatus() == ShopifyExcelUpload::PAYMENT_SETTLEMENT_STATUS_RETURNED;
    }

    public function getSettleDate() {
        return $this->payment[self::RECO][ShopifyExcelUpload::PaymentSettledDate];
    }

    public function isReconciled() {
        return ($this->isSettled() || $this->isReturned());
    }

    public function getRecoStatus() {
        return $this->payment[self::RECO][ShopifyExcelUpload::PaymentSettlementStatus] ?? ShopifyExcelUpload::PAYMENT_SETTLEMENT_STATUS_DEFAULT;
    }

    public function getRemarks() {
        return $this->payment[self::RECO][ShopifyExcelUpload::PaymentRemarks] ?? '';
    }

    public function getTransactionID() {
        return $this->payment[ShopifyExcelUpload::PaymentTransactionID];
    }

    public function getAmount() {
        return $this->payment[ShopifyExcelUpload::PaymentAmount];
    }

    public function isProcessed() {
        return $this->payment[ShopifyExcelUpload::PaymentProcessed] == 'Yes';
    }

    public function getIndex() {
        return $this->index;
    }

    public function getRefundedAmount() {
        return $this->payment[ShopifyExcelUpload::PaymentRefundAmount] ?? 0;
    }

    public function getRefundDate() {
        if(!empty($this->payment[ShopifyExcelUpload::PaymentRefundDate])) {
            return Carbon::createFromTimestamp($this->payment[ShopifyExcelUpload::PaymentRefundDate])->format(ShopifyExcelUpload::DATE_FORMAT);
        }
        return '';
    }

    public function isRefunded() {
        return ($this->getAmount() == $this->getRefundedAmount());
    }

    public function isPartialRefunded() {
        return ($this->getRefundedAmount() < $this->getAmount());
    }

    public function AvailableToRefund() {
        return ($this->getAmount() - $this->getRefundedAmount());
    }

    public function isCancelled() {
        return $this->payment[ShopifyExcelUpload::PaymentIsCanceled] ?? false;
    }

    public function getStatus() {
        $status = '';
        if($this->isProcessed()) {
            if($this->getRefundedAmount() == 0) {
                $status = 'Paid';
            } elseif($this->getRefundedAmount() < $this->getAmount()) {
                $status = 'Partial Refund';
            } else {
                $status = 'Full Refund';
            }
        } else {
            if($this->isCancelled()) {
                $status = 'Drop Out';
            } else {
                $status = 'UnPaid';
            }
        }

        return $status;
    }
}
