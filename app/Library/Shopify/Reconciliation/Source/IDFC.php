<?php
namespace App\Library\Shopify\Reconciliation\Source;

class IDFC extends Base {

    const SOURCE_CODE = 1;

    public static $columns = [
        'additional_information1', 'additional_information2', 'additional_information4', 'additional_information5', 'additional_information3',
        'instrument_numbermandate_reference_no', 'pickup_date', 'drawn_on_bank_name',
        'instrument_datedate_of_initiation', 'drawer_name',
        'instrumentinstruction_amount', 'drawn_on_bankdebit_ac_bank','date_of_depositdate_of_initiation', 'liquidation_status','liquidation_date',
        'return_date','return_amount', 'return_reason', 'drawer_ac_number', 'drawn_on_branchdebit_ac_branch', 'drawn_on_bankdebit_ac_bank'
    ];

    public static $columnTitles = [
        'drawer_name' => 'Acc Holder Name',
        'drawn_on_bank_name' => 'Bank',
        'drawer_ac_number' => 'Acc No',
        'instrument_numbermandate_reference_no' => 'Ch/DD No',
        'instrumentinstruction_amount' => 'Amount',
        'pickup_date' => 'Pickup On',
        'date_of_depositdate_of_initiation' => 'Deposit On',
        'liquidation_date' => 'Liquidation On',
        'return_date' => 'Return On',
        'liquidation_status' => 'Status',
    ];

    public function GetModeNumber(): string {
        return $this->row['instrument_numbermandate_reference_no'] ?? '';
    }

    public function GetBankCode(): string {
        return $this->row['drawn_on_bankdebit_ac_bank'] ?? '';
    }

    public function GetModeDate(): string {
        return !empty($this->row['instrument_datedate_of_initiation']) ? date('d/m/Y', strtotime($this->row['instrument_datedate_of_initiation'])) : '';
    }

    public function GetModeAmount(): string {
        return strval(round(str_replace(",","", $this->row['instrumentinstruction_amount']),0)) ;
    }

    public function IsReturned(): bool {
        return (strtolower($this->row['liquidation_status']) == 'return');
    }

    public function IsPaid(): bool {
        return (strtolower($this->row['liquidation_status']) == 'paid');
    }

    public function IsSettled(): bool {
        return !empty($this->GetLiquidationDate());
    }

    public function GetStatus(): string {
        return !empty($this->row['liquidation_status']) ? strtolower($this->row['liquidation_status']) : '';
    }

    public function GetReturnedDate(): int {
        return !empty($this->row['return_date']) ? strtotime($this->row['return_date']) : 0;
    }

    public function GetLiquidationDate(): int {
        return !empty($this->row['liquidation_date']) ? strtotime($this->row['liquidation_date']) : 0;
    }

    public function GetDepositDate(): int {
        return !empty($this->row['date_of_depositdate_of_initiation']) ? strtotime($this->row['date_of_depositdate_of_initiation']) : 0;
    }

    public function GetStudentID(): string {
        return $this->row['additional_information2'] ?? '';
    }

    public function GetMICRCode(): string {
        $start = substr($this->row['drawn_on_branchdebit_ac_branch'], 0, 3);
        $end = substr($this->row['drawn_on_branchdebit_ac_branch'], 3, strlen($this->row['drawn_on_branchdebit_ac_branch']));

        return $start . $this->row['drawn_on_bankdebit_ac_bank'] . $end;
    }

    public function IsDD(): bool {
        return ($this->row['drawer_ac_number'] == '9999999999999999');
    }

    public function IsCheque(): bool {
        return !$this->IsDD();
    }

    public function GetAccountNumber(): string {
        return $this->row['drawer_ac_number'] ?? '';
    }

    public function GetTransactionSource(): string {
        return 1;
    }

    public function IsValid(): bool {
        if(empty($this->GetModeNumber())
            || empty($this->GetModeDate())
            || empty($this->GetModeAmount())
            || empty($this->GetStudentID())
            || empty($this->GetMICRCode())
            || empty($this->GetDepositDate())
            || empty($this->GetStatus())
            || empty($this->GetBankCode())) {
            return false;
        }

        return true;
    }
}
