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

class EscalationReportExport implements FromCollection, WithHeadings, WithStyles, WithColumnWidths
{
    protected $data;

    public function __construct(Collection $data)
    {
        $this->data = $data;
    }

    public function collection(): Collection
    {
        return $this->data->map(function ($item) {
            // Sanitize remarks and escalation fields
            $remarks = !empty($item['Remarks']) ? preg_replace('/[^A-Za-z0-9\-]/', ' ', $item['Remarks']) : '';
            $escalation_reasons = !empty($item['Escalation_Reasons']) ? preg_replace('/[^A-Za-z0-9\-]/', ' ', $item['Escalation_Reasons']) : '';
            $escalation_remarks = !empty($item['Escalation_Remarks']) ? preg_replace('/[^A-Za-z0-9\-]/', ' ', $item['Escalation_Remarks']) : '';

            return [
                'Id' => $item['Id'] ?? '',
                'CVM_Id' => $item['CVM_Id'] ?? '',
                'Created_At' => $item['Created_At'] ?? '',
                'Request_Type' => $item['Request_Type'] ?? '',
                'Sub_Type' => $item['Sub_Type'] ?? '',
                'Assigned_Employee_Code' => $item['Assigned_Employee_Code'] ?? '',
                'Remarks' => $remarks,
                'Escalation_Count' => $item['Escalation_Count'] ?? '',
                'Escalation_Reasons' => $escalation_reasons,
                'Escalation_Remarks' => $escalation_remarks,
                'Current_Status' => $item['Current_Status'] ?? '',
                'Customer_First_Name' => $item['Customer_First_Name'] ?? '',
                'Customer_Last_Name' => $item['Customer_Last_Name'] ?? '',
                'Hospital_Name' => $item['Hospital_Name'] ?? '',
                'Department_Name' => $item['Department_Name'] ?? '',
                'State' => $item['State'] ?? '',
                'Responsible_Branch' => $item['Responsible_Branch'] ?? '',
            ];
        });
    }

    public function headings(): array
    {
        return [
            'Id',
            'CVM_Id',
            'Created_At',
            'Request_Type',
            'Sub_Type',
            'Assigned_Employee_Code',
            'Remarks',
            'Escalation_Count',
            'Escalation_Reasons',
            'Escalation_Remarks',
            'Current_Status',
            'Customer_First_Name',
            'Customer_Last_Name',
            'Hospital_Name',
            'Department_Name',
            'State',
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
                'bold' => true
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
            'A' => 10,  // Id
            'B' => 15,  // CVM_Id
            'C' => 20,  // Created_At
            'D' => 15,  // Request_Type
            'E' => 15,  // Sub_Type
            'F' => 20,  // Assigned_Employee_Code
            'G' => 30,  // Remarks
            'H' => 15,  // Escalation_Count
            'I' => 20,  // Escalation_Reasons
            'J' => 20,  // Escalation_Remarks
            'K' => 15,  // Current_Status
            'L' => 20,  // Customer_First_Name
            'M' => 20,  // Customer_Last_Name
            'N' => 25,  // Hospital_Name
            'O' => 20,  // Department_Name
            'P' => 12,  // State
            'Q' => 20,  // Responsible_Branch
        ];
    }
}
