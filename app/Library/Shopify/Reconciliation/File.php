<?php

namespace App\Library\Shopify\Reconciliation;

use App\Library\Shopify\Reconciliation\Source\IDFC;
use App\Library\Shopify\Reconciliation\Source\Manual;

/**
 * Class File
 */
class File
{
    /** @var $Headers  */
    public $Headers;

    /** @var $Rows */
    public $Rows;

    /** @var $FileType */
    public $filePath;

    /**
     * Register the source files here
     * @var array
     */
    public static $source = [
        IDFC::SOURCE_CODE => IDFC::class,
        Manual::SOURCE_CODE => Manual::class
    ];

    public static $sourceTitles = [
	    IDFC::SOURCE_CODE => 'Bank - IDFC',
        Manual::SOURCE_CODE => 'Manual Reconciliation'
    ];

    /**
     * File constructor.
     * @param array $Headers
     * @param array $Rows
     * @param string $FilePath
     * @param int $sourceCode
     *
     * @throws \Exception
     */
    public function __construct(array $Headers, array $Rows, string $FilePath, int $sourceCode) {
        if(!isset(self::$source[$sourceCode])) {
            throw new \Exception('Invalid source given');
        }

        $this->filePath = $FilePath;
        $this->Headers  = $Headers;
        $this->Rows     = $Rows;
        $this->sourceClass   = self::$source[$sourceCode];
    }

    public function GetSourceClass() : string {
        return $this->sourceClass;
    }

    public function GetRows() : array {
        return $this->Rows;
    }

    public function GetHeaders() : array {
        return $this->Headers;
    }

    public function GetFilePath() : string {
        return $this->filePath;
    }
}
