<?php


namespace App\Exports;

use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class CollectionExport implements FromCollection, WithHeadings
{
    private $data;
    private $break;
    use Exportable;

    public function __construct($break_by,$data = [])
    {
        $this->break = $break_by;
        $this->data = $data;
    }

    public function collection()
    {
        return collect($this->data);
    }

    public function headings(): array
    {
        if($this->break == 'product'){
            return ['Month','Product','order_count','txn_count','Total'];
        } elseif($this->break == 'branch'){
            return ['Month','Branch','order_count','txn_count','Total'];
        }
        return ['Month', 'Location','order_count', 'txn_count', 'Total'];
    }
}
