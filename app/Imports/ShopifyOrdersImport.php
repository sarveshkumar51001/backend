<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithCalculatedFormulas;
use Maatwebsite\Excel\Imports\HeadingRowFormatter;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Cell\DefaultValueBinder;
use PhpOffice\PhpSpreadsheet\Exception;

class ShopifyOrdersImport extends DefaultValueBinder implements WithCustomValueBinder, ToCollection, WithHeadingRow, WithCalculatedFormulas
{

    /**
     * @param Collection $rows
     * @return Collection
     */
    public function collection(Collection $rows)
    {
        return $rows;
    }

    public function headingRow(): int
    {
        return 2;
    }

    public function bindValue(Cell $cell, $value)
    {
        if (is_numeric($value)) {
            $cell->setValueExplicit($value, DataType::TYPE_STRING);
            return true;
        }

        // else return default behavior for the value
        return parent::bindValue($cell, $value);
    }

}
