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
            'Date of Enrollment',
            'Shopify Activity ID',
            'Delivery Institution',
            'Location',
            'School Name',
            'Student Name',
            'Activity Name',
            'Student Enrollment No',
            'Class',
            'Parent Name',
            'Activity Fee',
            'Scholarship/Discount',
            'Transaction Amount',
            'Transaction Mode',
            'Reference No(PayTM/NEFT)',
            'Cheque/DD No',
            'MICR Code',
            'Cheque/DD Date',
            'Drawee Name',
            'Drawee Account Number',
            'Bank Name',
            'Transaction Upload Date',
            'Payment Type',
            'Shopify Order Name',
            'Uploaded By',
            'Payment Status',
            'Reconciliation Status'
        ];
    }
}
