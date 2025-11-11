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

class CustomerListExport implements FromCollection, WithHeadings, WithStyles, WithColumnWidths
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
                'Id' => $item['Id'] ?? '',
                'Customer_Id' => $item['Customer_Id'] ?? '',
                'Title' => $item['Title'] ?? '',
                'First_Name' => $item['First_Name'] ?? '',
                'Last_Name' => $item['Last_Name'] ?? '',
                'Mobile_Number' => $item['Mobile_Number'] ?? '',
                'Email' => $item['Email'] ?? '',
                'Platform' => $item['Platform'] ?? '',
                'App_Version' => $item['App_Version'] ?? '',
                'Created_At' => $item['Created_At'] ?? '',
                'Region' => $item['Region'] ?? '',
                'Hospital_Names' => $item['Hospital_Names'] ?? '',
                'Departments' => $item['Departments'] ?? '',
                'City_Names' => $item['City_Names'] ?? '',
                'State_Sames' => $item['State_Sames'] ?? '',
            ];
        });
    }

    public function headings(): array
    {
        return [
            'Id',
            'Customer_Id',
            'Title',
            'First_Name',
            'Last_Name',
            'Mobile_Number',
            'Email',
            'Platform',
            'App_Version',
            'Created_At',
            'Region',
            'Hospital_Names',
            'Departments',
            'City_Names',
            'State_Sames'
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
            'B' => 15,  // Customer_Id
            'C' => 10,  // Title
            'D' => 15,  // First_Name
            'E' => 15,  // Last_Name
            'F' => 15,  // Mobile_Number
            'G' => 25,  // Email
            'H' => 12,  // Platform
            'I' => 15,  // App_Version
            'J' => 20,  // Created_At
            'K' => 12,  // Region
            'L' => 30,  // Hospital_Names
            'M' => 20,  // Departments
            'N' => 20,  // City_Names
            'O' => 15,  // State_Sames
        ];
    }
}
