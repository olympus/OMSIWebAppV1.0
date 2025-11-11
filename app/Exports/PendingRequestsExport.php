<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Illuminate\Support\Collection;

class PendingRequestsExport implements FromCollection, WithHeadings, WithStyles, ShouldAutoSize
{
    protected $data;

    public function __construct($data)
    {
        // Ensure we always have a collection of arrays
        $this->data = collect($data)->map(function ($item) {
            return is_object($item) ? (array) $item : $item;
        });
    }

    public function collection()
    {
        return $this->data;
    }

    public function headings(): array
    {
        if ($this->data->isEmpty()) {
            return ['No requests to display'];
        }

        return [
            'Id',
            'CVM_Id',
            'Request_Type',
            'Status',
            'Created_At',
            'Updated_At',
            'First_Name',
            'Last_Name',
            'Hospital_Names',
            'State',
            'Departments',
            'Responsible_Branch',
            'SFDC_Id',
            'Remarks'
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $rowCount = $this->data->count() + 1;

        // Header styling
        $sheet->getStyle('A1:N1')->getFont()->getColor()->setRGB('FFFFFF');
        $sheet->getStyle('A1:N1')->getFill()->setFillType('solid')
              ->getStartColor()->setRGB('4f81bd');

        // Alternate row background colors
        for ($i = 2; $i <= $rowCount; $i++) {
            $color = $i % 2 == 0 ? 'b8cce4' : 'dbe5f1';
            $sheet->getStyle("A{$i}:N{$i}")
                ->getFill()->setFillType('solid')
                ->getStartColor()->setRGB($color);
        }

        // Custom column width example (E column wider)
        $sheet->getColumnDimension('E')->setWidth(30);

        return [];
    }
}
