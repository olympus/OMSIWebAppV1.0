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

class RequestDataExport implements FromCollection, WithHeadings, WithStyles, WithColumnWidths
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
            $remarks = !empty($item->remarks) ? preg_replace('/[^A-Za-z0-9\-]/', ' ', $item->remarks) : '';
            $escalation_reasons = !empty($item->escalation_reasons) ? preg_replace('/[^A-Za-z0-9\-]/', ' ', $item->escalation_reasons) : '';
            $escalation_remarks = !empty($item->escalation_remarks) ? preg_replace('/[^A-Za-z0-9\-]/', ' ', $item->escalation_remarks) : '';

            // Get status timeline data
            $statusTimelineData = $item->statusTimelineData ?? collect();
            
            $received = $statusTimelineData->where('status', 'Received')->first();
            $assigned = $statusTimelineData->where('status', 'Assigned')->first();
            $attended = $statusTimelineData->where('status', 'Attended')->first();
            $quotation_prepared = $statusTimelineData->where('status', 'Quotation_Prepared')->first();
            $po_received = $statusTimelineData->where('status', 'PO_Received')->first();
            $dispatched = $statusTimelineData->where('status', 'Dispatched')->first();
            $received_at_repair_center = $statusTimelineData->where('status', 'Received_At_Repair_Center')->first();
            $repair_started = $statusTimelineData->where('status', 'Repair_Started')->first();
            $repair_completed = $statusTimelineData->where('status', 'Repair_Completed')->first();
            $ready_to_dispatch = $statusTimelineData->where('status', 'Ready_To_Dispatch')->first();
            $closed = $statusTimelineData->where('status', 'Closed')->first();

            return [
                'ID' => $item->id ?? '',
                'CVM ID' => $item->cvm_id ?? '',
                'Import Id' => $item->import_id ?? '',
                'Request Type' => $item->request_type ?? '',
                'Sub Type' => $item->sub_type ?? '',
                'Customer Id' => $item->customer_id ?? '',
                'Hospital Id' => $item->hospital_id ?? '',
                'Dept Id' => $item->dept_id ?? '',
                'Remarks' => $remarks,
                'Sap Id' => $item->sap_id ?? '',
                'Sfdc Id' => $item->sfdc_id ?? '',
                'Sfdc Customer Id' => $item->sfdc_customer_id ?? '',
                'Product Category' => $item->product_category ?? '',
                'Employee Code' => $item->employee_code ?? '',
                'Last Updated By' => $item->last_updated_by ?? '',
                'Status' => $item->status ?? '',
                'Is Escalated' => $item->is_escalated ?? '',
                'Escalation Count' => $item->escalation_count ?? '',
                'Escalation Assign1' => $item->escalation_assign1 ?? '',
                'Escalation Assign2' => $item->escalation_assign2 ?? '',
                'Escalation Assign3' => $item->escalation_assign3 ?? '',
                'Escalation Assign4' => $item->escalation_assign4 ?? '',
                'Escalation Reasons' => $escalation_reasons,
                'Escalation Remarks' => $escalation_remarks,
                'Escalated At' => $item->escalated_at ?? '',
                'Feedback Id' => $item->feedback_id ?? '',
                'Feedback Requested' => $item->feedback_requested ?? '',
                'Is Practice' => $item->is_practice ?? '',
                'Created At' => $item->created_at ? $item->created_at->format('d/m/Y H:i:s') : '',
                'Updated At' => $item->updated_at ? $item->updated_at->format('d/m/Y H:i:s') : '',
                'First Name' => $item->customer->first_name ?? '',
                'Last Name' => $item->customer->last_name ?? '',
                'Email' => $item->customer->email ?? '',
                'Sap Customer Id' => $item->customer->sap_customer_id ?? '',
                'Hospital Name' => $item->hospital->hospital_name ?? '',
                'State' => $item->hospital->state ?? '',
                'Responsible Branch' => $item->hospital->responsible_branch ?? '',
                'Department Name' => $item->departmentData->name ?? '',
                'Received' => $received ? $received->created_at->format('d-m-y h:i:A') : '',
                'Assigned' => $assigned ? $assigned->created_at->format('d-m-y h:i:A') : '',
                'Attended' => $attended ? $attended->created_at->format('d-m-y h:i:A') : '',
                'Received_At_Repair_Center' => $received_at_repair_center ? $received_at_repair_center->created_at->format('d-m-y h:i:A') : '',
                'Quotation_Prepared' => $quotation_prepared ? $quotation_prepared->created_at->format('d-m-y h:i:A') : '',
                'PO_Received' => $po_received ? $po_received->created_at->format('d-m-y h:i:A') : '',
                'Repair_Started' => $repair_started ? $repair_started->created_at->format('d-m-y h:i:A') : '',
                'Repair_Completed' => $repair_completed ? $repair_completed->created_at->format('d-m-y h:i:A') : '',
                'Ready_To_Dispatch' => $ready_to_dispatch ? $ready_to_dispatch->created_at->format('d-m-y h:i:A') : '',
                'Dispatched' => $dispatched ? $dispatched->created_at->format('d-m-y h:i:A') : '',
                'Closed' => $closed ? $closed->created_at->format('d-m-y h:i:A') : '',
            ];
        });
    }

    public function headings(): array
    {
        return [
            'ID',
            'CVM ID',
            'Import Id',
            'Request Type',
            'Sub Type',
            'Customer Id',
            'Hospital Id',
            'Dept Id',
            'Remarks',
            'Sap Id',
            'Sfdc Id',
            'Sfdc Customer Id',
            'Product Category',
            'Employee Code',
            'Last Updated By',
            'Status',
            'Is Escalated',
            'Escalation Count',
            'Escalation Assign1',
            'Escalation Assign2',
            'Escalation Assign3',
            'Escalation Assign4',
            'Escalation Reasons',
            'Escalation Remarks',
            'Escalated At',
            'Feedback Id',
            'Feedback Requested',
            'Is Practice',
            'Created At',
            'Updated At',
            'First Name',
            'Last Name',
            'Email',
            'Sap Customer Id',
            'Hospital Name',
            'State',
            'Responsible Branch',
            'Department Name',
            'Received',
            'Assigned',
            'Attended',
            'Received_At_Repair_Center',
            'Quotation_Prepared',
            'PO_Received',
            'Repair_Started',
            'Repair_Completed',
            'Ready_To_Dispatch',
            'Dispatched',
            'Closed',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $highestRow = $sheet->getHighestRow();
        
        // Header row styling (row 1)
        $sheet->getStyle('A1:AW1')->applyFromArray([
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
            $sheet->getStyle('A' . $row . ':AW' . $row)->applyFromArray([
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
            'A' => 10,  // ID
            'B' => 15,  // CVM ID
            'C' => 15,  // Import Id
            'D' => 15,  // Request Type
            'E' => 20,  // Sub Type
            'F' => 12,  // Customer Id
            'G' => 12,  // Hospital Id
            'H' => 30,  // Remarks
            'I' => 12,  // Dept Id
            'J' => 15,  // Sap Id
        ];
    }
}