<?php

namespace App\Library\Shopify\Reconciliation\Source;

abstract class Base {

    /** @var array */
    protected $row = [];
    public static $columns = [];
    public static $columnTitles = [];

    public function __construct(array $row) {
        foreach ($row as $key => $value) {
            $this->row[$key] = trim(str_replace(array('\'', '"'), '', $value));
        }
    }

    public static function GetColumns(): array {
        return static::$columns;
    }

    public static function GetColumnTitles(): array {
        return static::$columnTitles;
    }

    // Unused functions for online mode

    /**
     * @throws \Exception
     */
    public function GetAccountNumber(): string {
        throw new \Exception('Method not implemented');
    }

    /**
     * @throws \Exception
     */
    public function IsDD(): bool {
        throw new \Exception('Method not implemented');
    }

    /**
     * @throws \Exception
     */
    public function IsCheque(): bool {
        throw new \Exception('Method not implemented');
    }

    /**
     * @throws \Exception
     */
    public function GetMICRCode(): string {
        throw new \Exception('Method not implemented');
    }

    /**
     * @throws \Exception
     */
    public function IsReturned(): bool {
        throw new \Exception('Method not implemented');
    }

    /**
     * @throws \Exception
     */
    public function GetReturnedDate(): int {
        throw new \Exception('Method not implemented');
    }
}
