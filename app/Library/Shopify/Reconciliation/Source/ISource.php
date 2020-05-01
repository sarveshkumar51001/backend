<?php

namespace App\Library\Shopify\Reconciliation\Source;

interface ISource {
    public function __construct(array $row);
    public static function GetColumns() : array;
    public static function GetColumnTitles() : array;
    public function IsDD() : bool;
    public function IsCheque() : bool;
    public function GetMICRCode() : string;
    public function GetAccountNumber() : string;
    public function GetModeNumber() : string;
    public function GetModeDate() : string;
    public function GetBankCode() : string;
    public function GetStudentID() : string;
    public function GetModeAmount() : float;
    public function GetStatus(): string;

    public function IsReturned() : bool;
    public function IsSettled() : bool;
    public function IsPaid() : bool;
    public function GetReturnedDate() : int;
    public function GetLiquidationDate(): int;
    public function GetDepositDate(): int;
    public function GetTransactionSource(): string;

    public function IsValid(): bool;
}
