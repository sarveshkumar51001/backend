<?php

namespace App\Exports;

use App\Models\ShopifyExcelUpload;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;


class ReportExport implements FromCollection, WithHeadings
{
    private $data;
    use Exportable;

    public function __construct($data =[]){

        $this->data = $data;
    }

    public function collection()
    {
        return collect($this->data);
    }

    public function headings(): array
    {
        return ShopifyExcelUpload::CHEQUE_REPORT_KEYS;
    }
}
