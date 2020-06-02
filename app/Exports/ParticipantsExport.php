<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ParticipantsExport implements FromCollection, WithHeadings
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
            'Session Name',
            'Session Date',
            'Session Slot',
            'Student Name',
            'Student Phone',
            'Student Email'
            ];
    }
}
