<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithCalculatedFormulas;
use Maatwebsite\Excel\Imports\HeadingRowFormatter;

class ReconcilationImport implements ToCollection, WithHeadingRow, WithCalculatedFormulas
{

    /**
     * @param Collection $collection
     */
    public function collection(Collection $rows)
    {
        return $rows;
    }

}
