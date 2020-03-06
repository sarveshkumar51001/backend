<?php
namespace App\Library\Shopify\Reconciliation;

use App\Models\ShopifyExcelUpload;

class Payment {

    private $payment;

    private $index;

    const RECO = 'reconcilation';

    public function __construct(array $payment, int $index)
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

    public function getAmount() {
        return $this->payment[ShopifyExcelUpload::PaymentAmount];
    }

    public function isProcessed() {
        return $this->payment[ShopifyExcelUpload::PaymentProcessed] == 'Yes';
    }

    public function getIndex() {
        return $this->index;
    }
}
