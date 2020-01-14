<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class TransactionsExport implements FromCollection, WithHeadings
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
        return [
            'Activity Name',
            'Activity Fee',
            'Location',
            'Student Enrollment No',
            'Student Name',
            'Class',
            'Shopify Order Name',
            'Uploaded By',
            'Transaction Amount',
            'Transaction Mode',
            'Cheque/DD No',
            'Cheque/DD Date',
            'Reference No(PayTM/NEFT)',
            'Transaction Upload Date'
        ];
    }
}
