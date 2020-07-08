<?php

namespace App\Library\Shopify\Reconciliation\Source;

class Manual extends Base {

    const SOURCE_CODE = 2;

    public static $columns = [
        'transaction_id', 'remarks', 'reconciliation_status'
    ];

    public function GetTransactionRemark(): string {
        return $this->row['remarks'] ?? '';
    }

    public function GetReconciliationStatus(): string {
        return strtolower($this->row['reconciliation_status']) ?? '';
    }

    public function GetTransactionID(): string {
            return $this->row['transaction_id'] ?? '';
    }

    public function IsValid(): bool {
        if(empty($this->GetTransactionID())
            || empty($this->GetTransactionRemark())
            || empty($this->GetReconciliationStatus())) {
            return false;
        }
        return true;
    }


}
