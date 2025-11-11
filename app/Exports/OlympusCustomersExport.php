<?php

namespace App\Exports;

use App\Models\Hospitals;
use App\Models\Departments;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Font;

class OlympusCustomersExport implements FromArray, WithHeadings, WithStyles, WithColumnWidths, ShouldAutoSize
{
    protected $customers;

    public function __construct($customers)
    {
        $this->customers = $customers;
    }

    public function headings(): array
    {
        return [
            'Id',
            'Customer ID',
            'SAP Customer ID',
            'Title',
            'First Name',
            'Last Name',
            'Mobile',
            'Email',
            'Verified',
            'Otp Code',
            'Hospital Id',
            'Platform',
            'App Version',
            'Created At',
            'Updated At',
            'Cities',
            'States',
            'Region',
            'Branch',
            'Hospital Names',
            'Departments'
        ];
    }

    public function array(): array
    {
        $data = [];

        foreach ($this->customers as $customer) {
            $hospitals = Hospitals::where('customer_id', $customer->id)->get();
            $hospitalNames = $hospitals->pluck('hospital_name')->implode(', ');
            $cities = $hospitals->pluck('city')->implode(', ');
            $states = $hospitals->pluck('state')->implode(', ');
            $region = [];
            $branch = [];

            $allDepartments = [];
            foreach ($hospitals as $hospital) {
                $deptIds = explode(',', $hospital->dept_id);
                $departments = Departments::whereIn('id', $deptIds)->pluck('name')->toArray();
                $allDepartments = array_merge($allDepartments, $departments);
                $region[] = ucfirst(find_region($hospital->state));
                $branch[] = $hospital->responsible_branch;
            }
            $allDepartments = array_unique($allDepartments);
            $allRegions = implode(',', array_unique($region));
            $allBranch = implode(',', array_unique($branch));

            $data[] = [
                'Id' => $customer->id,
                'Customer ID' => $customer->customer_id,
                'SAP Customer ID' => $customer->sap_customer_id,
                'Title' => $customer->title,
                'First Name' => $customer->first_name,
                'Last Name' => $customer->last_name,
                'Mobile' => $customer->mobile_number,
                'Email' => $customer->email,
                'Verified' => $customer->is_verified ? 'Yes' : 'No',
                'Otp Code' => $customer->otp_code,
                'Hospital Id' => $customer->hospital_id,
                'Platform' => $customer->platform,
                'App Version' => $customer->app_version,
                'Created At' => $customer->created_at,
                'Updated At' => $customer->updated_at,
                'Cities' => $cities ?: '-',
                'States' => $states ?: '-',
                'Region' => $allRegions ?: '-',
                'Branch' => $allBranch ?: '-',
                'Hospital Names' => $hospitalNames ?: '-',
                'Departments' => implode(', ', $allDepartments) ?: '-',
            ];
        }

        return $data;
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('A1:T1')->applyFromArray([
            'font' => [
                'color' => ['rgb' => 'FFFFFF'],
                'size' => 10,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '4F81BD'],
            ],
        ]);

        $data = $this->array();
        foreach ($data as $key => $value) {
            $rowNumber = $key + 2; // +1 for heading, +1 for 0-index
            if ($key % 2 == 0) {
                $sheet->getStyle('A' . $rowNumber . ':T' . $rowNumber)->applyFromArray([
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'B8CCE4'],
                    ],
                ]);
            } else {
                $sheet->getStyle('A' . $rowNumber . ':T' . $rowNumber)->applyFromArray([
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'DBE5F1'],
                    ],
                ]);
            }
        }

        $sheet->getStyle($sheet->calculateWorksheetDimension())->applyFromArray([
            'font' => ['size' => 10],
        ]);

        return [];
    }

    public function columnWidths(): array
    {
        return [
            'D' => 30,
            'F' => 20,
        ];
    }
}
