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

class VoiceOfCustomerExport implements FromCollection, WithHeadings, WithStyles, WithColumnWidths
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
            $remarks = !empty($item['Remarks']) ? preg_replace('/[^A-Za-z0-9\-]/', ' ', $item['Remarks']) : '';

            return [
                'Id' => $item['Id'] ?? '',
                'MyVoiceId' => $item['MyVoiceId'] ?? '',
                'Created_At' => $item['Created_At'] ?? '',
                'Request_Type' => $item['Request_Type'] ?? '',
                'Sub_Type' => $item['Sub_Type'] ?? '',
                'Current_Status' => $item['Current_Status'] ?? '',
                'Hospital_Name' => $item['Hospital_Name'] ?? '',
                'Department_Name' => $item['Department_Name'] ?? '',
                'State' => $item['State'] ?? '',
                'Remarks' => $remarks,
                'Customer_First_Name' => $item['Customer_First_Name'] ?? '',
                'Customer_Last_Name' => $item['Customer_Last_Name'] ?? '',
                'Region' => $item['Region'] ?? '',
                'Assigned_Employee_Name' => $item['Assigned_Employee_Name'] ?? '',
                'Responsible_Branch' => $item['Responsible_Branch'] ?? '',
            ];
        });
    }

    public function headings(): array
    {
        return [
            'Id',
            'MyVoiceId',
            'Created_At',
            'Request_Type',
            'Sub_Type',
            'Current_Status',
            'Hospital_Name',
            'Department_Name',
            'State',
            'Remarks',
            'Customer_First_Name',
            'Customer_Last_Name',
            'Region',
            'Assigned_Employee_Name',
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
            'B' => 15,  // MyVoiceId
            'C' => 20,  // Created_At
            'D' => 15,  // Request_Type
            'E' => 15,  // Sub_Type
            'F' => 15,  // Current_Status
            'G' => 25,  // Hospital_Name
            'H' => 20,  // Department_Name
            'I' => 12,  // State
            'J' => 30,  // Remarks
            'K' => 20,  // Customer_First_Name
            'L' => 20,  // Customer_Last_Name
            'M' => 12,  // Region
            'N' => 25,  // Assigned_Employee_Name
            'O' => 20,  // Responsible_Branch
        ];
    }
}
