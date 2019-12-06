<?php

namespace App\Imports\Binder;

use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Cell\DefaultValueBinder;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;

class CustomValueBinder extends DefaultValueBinder implements WithCustomValueBinder
{
    public function bindValue(Cell $cell, $value = null)
    {
        if (is_numeric($value))
        {
            $cell->setValueExplicit($value, DataType::TYPE_STRING);

            return true;
        }
        // else return default behavior
        return parent::bindValue($cell, $value);
    }
}
