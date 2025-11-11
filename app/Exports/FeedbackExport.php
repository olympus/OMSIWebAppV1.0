<?php

namespace App\Exports;

use App\Models\Feedback;
use App\Models\ServiceRequests;
use App\Models\ArchiveServiceRequests;
use App\Models\Customers;
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

class FeedbackExport implements FromArray, WithHeadings, WithStyles, WithColumnWidths, ShouldAutoSize
{
    protected $feedbacks;

    public function __construct($feedbacks)
    {
        $this->feedbacks = $feedbacks;
    }

    public function headings(): array
    {
        return [
            'Request_Id',
            'Request_Type',
            'Sub_Type',
            'Created_At',
            'Response_Speed',
            'Quality_Of_Response',
            'App_Experience',
            'Olympus_Staff_Performance',
            'Hospital',
            'Department',
            'City',
            'State',
            'First_Name',
            'Last_Name',
            'Assigned Employee Name',
            'Assigned_Engineer',
            'Employee_Code',
            'Responsible_Branch'
        ];
    }

    public function array(): array
    {
        $data = [];

        foreach ($this->feedbacks as $feedback) {
            $customer = $this->getCustomer($feedback);
            $hospitals = implode(', ', array_map(fn($h) => $h->hospital_name ?? '-', $this->getHospitals($feedback)));

            // Departments
            $departmentsArr = [];
            foreach ($this->getHospitals($feedback) as $hospital) {
                $deptIds = explode(',', $hospital->dept_id);
                $departmentsArr = array_merge($departmentsArr, Departments::whereIn('id', $deptIds)->pluck('name')->toArray());
            }
            $departments = implode(', ', array_unique($departmentsArr));

            // Cities & States
            $cities = implode(', ', array_unique(array_map(fn($h) => $h->city ?? '-', $this->getHospitals($feedback))));
            $states = implode(', ', array_unique(array_map(fn($h) => $h->state ?? '-', $this->getHospitals($feedback))));

            // Sub Type
            $serviceRequest = ServiceRequests::find($feedback->request_id)
                ?? ArchiveServiceRequests::find($feedback->request_id);
            $requestType = $serviceRequest?->request_type ?? '-';
            $subType = $serviceRequest?->sub_type ?? '-';

            $assigned_employee = $serviceRequest?->employeeData ?? null;
            $assign = isset($assigned_employee->name) ? $assigned_employee->name . " employee assigned for this Feedback ID $feedback->id" : "Assigned employee not found for Feedback ID $feedback->id";

            $data[] = [
                $feedback->request_id,
                $requestType,
                $subType,
                $feedback->created_at,
                $feedback->response_speed,
                $feedback->quality_of_response,
                $feedback->app_experience,
                $feedback->olympus_staff_performance,
                $hospitals ?: '-',
                $departments ?: '-',
                $cities ?: '-',
                $states ?: '-',
                $customer?->first_name ?? '-',
                $customer?->last_name ?? '-',
                $assign,
                $assigned_employee?->name ?? '-',
                $assigned_employee?->employee_code ?? '-',
                $serviceRequest->hospital->responsible_branch ?? '-'
            ];
        }

        return $data;
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('A1:R1')->applyFromArray([
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
                $sheet->getStyle('A' . $rowNumber . ':R' . $rowNumber)->applyFromArray([
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'B8CCE4'],
                    ],
                ]);
            } else {
                $sheet->getStyle('A' . $rowNumber . ':R' . $rowNumber)->applyFromArray([
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

    // Get customer from ServiceRequests or ArchiveServiceRequests
    private function getCustomer($record)
    {
        $serviceRequest = ServiceRequests::find($record->request_id)
            ?? ArchiveServiceRequests::find($record->request_id);

        return $serviceRequest ? Customers::find($serviceRequest->customer_id) : null;
    }

    // Get hospitals for a given request
    private function getHospitals($record): array
    {
        $serviceRequest = ServiceRequests::find($record->request_id)
            ?? ArchiveServiceRequests::find($record->request_id);

        if (!$serviceRequest) {
            return [];
        }
        $hospitalIds = explode(',', $serviceRequest->hospital_id);
        return Hospitals::whereIn('id', $hospitalIds)->get()->all();
    }
}
