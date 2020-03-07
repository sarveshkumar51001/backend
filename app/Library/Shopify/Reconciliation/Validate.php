<?php

namespace App\Library\Shopify\Reconciliation;

/**
 * Class Validate
 */
class Validate
{
    public $File;

    /**
     * Validate constructor.
     * @param File $File
     */
    public function __construct(File $File) {
        $this->File = $File;
    }

    public function Run() {
        $errors = [];

        /* @var ISource $sourceClass */
        $sourceClass = $this->File->sourceClass;
        $missingHeaders = array_diff($sourceClass::GetColumns(), $this->File->GetHeaders());
        if(count($missingHeaders)) {
            $errors[] = sprintf("%s column(s) is missing", implode(',', $missingHeaders));
        }
        foreach ($this->File->GetRows() as $row) {
            foreach ($sourceClass::GetColumns() as $column) {
                if (!array_key_exists($column, $row)) {
                    $errors[] = sprintf("%s key is missing", $column);
                }
            }

            /* @var ISource $Source */
            $Source = new $sourceClass($row);
            if(!$Source->IsValid()) {
                $errors[] = sprintf("Empty data for required fields");
            }
        }

        return $errors;
    }
}
