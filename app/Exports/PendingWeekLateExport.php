<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Color;

class PendingWeekLateExport implements FromCollection, WithHeadings, WithStyles, WithColumnWidths
{
    protected $data;

    public function __construct(Collection $data)
    {
        $this->data = $data;
    }

    public function collection(): Collection
    {
        return $this->data->map(function ($item) {
            // Sanitize remarks
            $remarks = !empty($item->Remarks) ? preg_replace('/[^A-Za-z0-9\-]/', ' ', $item->Remarks) : '';

            return [
                'Id' => $item->Id ?? '',
                'CVM_Id' => $item->CVM_Id ?? '',
                'SAP_Id' => $item->SAP_Id ?? '',
                'SFDC_Id' => $item->SFDC_Id ?? '',
                'Request_Type' => $item->Request_Type ?? '',
                'Remarks' => $remarks,
                'Status' => $item->Status ?? '',
                'Created_At' => $item->Created_At ?? '',
                'Updated_At' => $item->Updated_At ?? '',
                'First_Name' => $item->First_Name ?? '',
                'Last_Name' => $item->Last_Name ?? '',
                'Hospital_Names' => $item->Hospital_Names ?? '',
                'State' => $item->State ?? '',
                'Departments' => $item->Departments ?? '',
                'Responsible_Branch' => $item->Responsible_Branch ?? '',
            ];
        });
    }

    public function headings(): array
    {
        return [
            'Id',
            'CVM_Id',
            'SAP_Id',
            'SFDC_Id',
            'Request_Type',
            'Remarks',
            'Status',
            'Created_At',
            'Updated_At',
            'First_Name',
            'Last_Name',
            'Hospital_Names',
            'State',
            'Departments',
            'Responsible_Branch'
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $highestRow = $sheet->getHighestRow();

        // Header row styling (row 1)
        $sheet->getStyle('A1:O1')->applyFromArray([
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'color' => ['rgb' => '4f81bd']
            ],
            'font' => [
                'color' => ['rgb' => 'FFFFFF'],
                'bold' => true
            ]
        ]);

        // Alternating row colors
        for ($row = 2; $row <= $highestRow; $row++) {
            $color = ($row % 2 == 0) ? 'b8cce4' : 'dbe5f1';
            $sheet->getStyle('A' . $row . ':O' . $row)->applyFromArray([
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'color' => ['rgb' => $color]
                ]
            ]);
        }

        return [];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 10,  // Id
            'B' => 15,  // CVM_Id
            'C' => 15,  // SAP_Id
            'D' => 15,  // SFDC_Id
            'E' => 15,  // Request_Type
            'F' => 30,  // Remarks
            'G' => 12,  // Status
            'H' => 20,  // Created_At
            'I' => 20,  // Updated_At
            'J' => 15,  // First_Name
            'K' => 15,  // Last_Name
            'L' => 20,  // Hospital_Names
            'M' => 12,  // State
            'N' => 15,  // Departments
            'O' => 20,  // Responsible_Branch
        ];
    }
}
