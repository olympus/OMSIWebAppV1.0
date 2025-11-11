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

class FeedbackReportExport implements FromCollection, WithHeadings, WithStyles, WithColumnWidths
{
    protected $data;

    public function __construct(Collection $data)
    {
        $this->data = $data;
    }

    public function collection(): Collection
    {
        return $this->data->map(function ($item) {
            return [
                'Request_Id' => $item['Request_Id'] ?? '',
                'Created_At' => $item['Created_At'] ?? '',
                'Region' => $item['Region'] ?? '',
                'Hospital' => $item['Hospital'] ?? '',
                'Department' => $item['Department'] ?? '',
                'City' => $item['City'] ?? '',
                'State' => $item['State'] ?? '',
                'First_Name' => $item['First_Name'] ?? '',
                'Last_Name' => $item['Last_Name'] ?? '',
                'Assigned_Engineer' => $item['Assigned_Engineer'] ?? '',
                'Response_Speed' => $item['Response_Speed'] ?? '',
                'Quality_Of_Response' => $item['Quality_Of_Response'] ?? '',
                'App_Experience' => $item['App_Experience'] ?? '',
                'Olympus_Staff_Performance' => $item['Olympus_Staff_Performance'] ?? '',
                'Request_Type' => $item['Request_Type'] ?? '',
                'Sub_Type' => $item['Sub_Type'] ?? '',
                'Responsible_Branch' => $item['Responsible_Branch'] ?? '',
            ];
        });
    }

    public function headings(): array
    {
        return [
            'Request_Id',
            'Created_At',
            'Region',
            'Hospital',
            'Department',
            'City',
            'State',
            'First_Name',
            'Last_Name',
            'Assigned_Engineer',
            'Response_Speed',
            'Quality_Of_Response',
            'App_Experience',
            'Olympus_Staff_Performance',
            'Request_Type',
            'Sub_Type',
            'Responsible_Branch'
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $highestRow = $sheet->getHighestRow();

        // Header row styling (row 1)
        $sheet->getStyle('A1:Q1')->applyFromArray([
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'color' => ['rgb' => '4f81bd']
            ],
            'font' => [
                'color' => ['rgb' => 'FFFFFF'],
                'bold' => true,
                'size' => 9
            ]
        ]);

        // Alternating row colors
        for ($row = 2; $row <= $highestRow; $row++) {
            $color = ($row % 2 == 0) ? 'b8cce4' : 'dbe5f1';
            $sheet->getStyle('A' . $row . ':Q' . $row)->applyFromArray([
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
            'A' => 12,  // Request_Id
            'B' => 20,  // Created_At
            'C' => 12,  // Region
            'D' => 30,  // Hospital
            'E' => 15,  // Department
            'F' => 15,  // City
            'G' => 12,  // State
            'H' => 15,  // First_Name
            'I' => 15,  // Last_Name
            'J' => 20,  // Assigned_Engineer
            'K' => 15,  // Response_Speed
            'L' => 20,  // Quality_Of_Response
            'M' => 15,  // App_Experience
            'N' => 25,  // Olympus_Staff_Performance
            'O' => 15,  // Request_Type
            'P' => 15,  // Sub_Type
            'Q' => 20,  // Responsible_Branch
        ];
    }
}
