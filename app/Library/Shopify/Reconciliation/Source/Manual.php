<?php

namespace App\Library\Shopify\Reconciliation\Source;

class Manual extends Base {

    const SOURCE_CODE = 2;

    public static $columns = [
        'transaction_id', 'remarks', 'reconciliation_status', 'transaction_amount' , 'chequedd_date'
    ];

    public function GetTransactionRemark(): string {
        return $this->row['remarks'] ?? '';
    }

    public function GetReconciliationStatus(): string {
        return strtolower($this->row['reconciliation_status']) ?? '';
    }

    public function GetModeAmount(): string {
        return $this->row['transaction_amount'];
    }

    public function GetModeDate(): string {
        return !empty($this->row['chequedd_date']) ? date('d/m/Y', strtotime($this->row['chequedd_date'])) : '';
    }

    public function GetTransactionID(): string {
            return $this->row['transaction_id'] ?? '';
    }

    public function GetStudentID(): string {
        return $this->row['school_enrollment_no'] ?? '';
    }

    public function IsValid(): bool {
        if(empty($this->GetTransactionID())
            || empty($this->GetTransactionRemark())
            || empty($this->GetReconciliationStatus())
            || empty($this->GetModeAmount())) {
            return false;
        }
        return true;
    }


}
