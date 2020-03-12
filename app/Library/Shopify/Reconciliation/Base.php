<?php

namespace App\Library\Shopify\Reconciliation;

use App\Library\Shopify\Reconciliation;
use App\Models\Activity;
use App\Models\Transaction;
use App\Models\Bill\Payment;
use App\Models\CourseCategory;
use App\Models\ReconcileStatement;
use App\Library\Transaction\Handler\Credit;
use App\Library\Transaction\Handler\Returned;

use Ramsey\Uuid\Uuid;

/**
 * Class Process
 */
abstract class Base
{
    /** @var File */
    public $File;

    /** @var $mode */
    public $mode;

    /** @var CourseCategory */
    public $Org;

    /** @var array */
    public $success  = [];
    public $errors   = [];
    public $metadata = [];

    /** @var array */
    public $SettledModes = [];
    public $Transactions = [];
    protected $txnIDList = [];

    const TOTAL_ROWS_COUNT           = 'total_rows_count';
    const SETTLED_ROWS_COUNT         = 'total_settled_rows_count';
    const ALREADY_SETTLED_ROWS_COUNT = 'already_settled_rows_count';
    const SETTLED_TRANSACTIONS_COUNT = 'settled_transactions_count';
    const RETURNED_ROWS_COUNT        = 'returned_rows_count';
    const FAILED_ROWS_COUNT          = 'failed_rows_count';
    const NOT_FOUND_ROWS_COUNT       = 'not_found_rows_count';
    const FILE_AMOUNT                = 'file_amount';
    const FILE_SETTLEABLE_AMOUNT     = 'file_settleable_amount';
    const ALREADY_SETTLED_AMOUNT     = 'already_settled_amount';
    const FAILED_AMOUNT              = 'failed_amount';
    const NOT_FOUND_AMOUNT           = 'not_found_amount';

    /**
     * Reconcile constructor.
     *
     * @param File $File
     * @param int $mode
     *
     * @throws \Exception
     */
    public function __construct(File $File, $mode = Reconcile::MODE_SANDBOX) {
        if(!Reconcile::IsValidMode($mode)) {
            throw new \Exception('Invalid mode given');
        }

        $this->File = $File;
        $this->mode = $mode;

        // Init metadata
        $this->metadata = [
            self::TOTAL_ROWS_COUNT => count($File->Rows),
            self::SETTLED_ROWS_COUNT => 0,
            self::SETTLED_TRANSACTIONS_COUNT => 0,
            self::ALREADY_SETTLED_ROWS_COUNT => 0,
            self::RETURNED_ROWS_COUNT => 0,
            self::FAILED_ROWS_COUNT => 0,
            self::NOT_FOUND_ROWS_COUNT => 0,
            self::FILE_AMOUNT => 0,
            self::FILE_SETTLEABLE_AMOUNT => 0,
            self::ALREADY_SETTLED_AMOUNT => 0,
            self::FAILED_AMOUNT => 0,
            self::NOT_FOUND_AMOUNT => 0
        ];
    }

    public function SetOrganization(CourseCategory $Org) {
    	$this->Org = $Org;
    }

    public function GetOrganization() {
    	return $this->Org;
    }

    /**
     * Return metadata results
     * @return array
     */
    public function GetMetadata() : array {
        return $this->metadata;
    }

    /**
     * If the reconcile is being done in sandbox mode, which means dry run
     * @return bool
     */
    public function IsSandbox() : bool {
        return $this->mode == Reconcile::MODE_SANDBOX;
    }

    /**
     * Primary function that take care of everything
     *
     * @throws \Exception
     */
    public function Run() {
        $result = [];
        foreach ($this->File->GetRows() as $row) {
            $result[] = $this->CheckAndMarkSettled($row);
        }

        if(!$this->IsSandbox()) {
            $this->StoreDataWithMetrics($result);
        }

        return [$result, $this->metadata];
    }

    abstract function CheckAndMarkSettled(array $row);

    /**
     * Save metadata into table for future analysis
     *
     * @param array $data
     */
    protected function StoreDataWithMetrics(array $data) {
        $this->metadata[self::TOTAL_ROWS_COUNT] = count($this->File->GetRows());

        $this->metadata['success'] = $this->success;
        $this->metadata['failed'] = $this->errors;

        ReconcileStatement::create([
            ReconcileStatement::UUID => Uuid::uuid4(),
            ReconcileStatement::FileName => $this->File->GetFilePath(),
            ReconcileStatement::RawData => json_encode($data),
            ReconcileStatement::MetaData => json_encode($this->metadata),
            ReconcileStatement::Status => (count($this->success) ? 1 : 0),
            ReconcileStatement::Source => array_flip(File::$source)[$this->File->GetSourceClass()],
            ReconcileStatement::ImportedAt => time(),
            ReconcileStatement::ImportedBy => (\Auth::user()->id ?? 0)
        ]);
    }
}
