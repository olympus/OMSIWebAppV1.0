<?php

namespace App\Filament\Exports;

use App\Models\CombinedServiceRequests;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;
use Illuminate\Support\Number;

class CombinedServiceRequestsExporter extends Exporter
{
    protected static ?string $model = CombinedServiceRequests::class;
    public function queue(): bool
    {
        return true; // 👈 important for notifications
    }
    public static function getColumns(): array
    {
        return [
            ExportColumn::make('id')
                ->label('ID'),
            ExportColumn::make('cvm_id'),
            ExportColumn::make('import_id'),
            ExportColumn::make('request_type'),
            ExportColumn::make('sub_type'),
            ExportColumn::make('customer_id'),
            ExportColumn::make('hospital_id'),
            ExportColumn::make('dept_id'),
            ExportColumn::make('remarks'),
            ExportColumn::make('closure_remarks'),
            ExportColumn::make('sap_id'),
            ExportColumn::make('sfdc_id'),
            ExportColumn::make('sfdc_customer_id'),
            ExportColumn::make('product_category'),
            ExportColumn::make('employee_code'),
            ExportColumn::make('last_updated_by'),
            ExportColumn::make('status'),
            ExportColumn::make('is_escalated'),
            ExportColumn::make('escalation_count'),
            ExportColumn::make('escalation_assign1'),
            ExportColumn::make('escalation_assign2'),
            ExportColumn::make('escalation_assign3'),
            ExportColumn::make('escalation_assign4'),
            ExportColumn::make('escalation_reasons'),
            ExportColumn::make('escalation_remarks'),
            ExportColumn::make('escalated_at'),
            ExportColumn::make('feedback_id'),
            ExportColumn::make('feedback_requested'),
            ExportColumn::make('is_practice'),
            ExportColumn::make('created_at'),
            ExportColumn::make('updated_at'),
            ExportColumn::make('acknowledgement_updated_at'),
            ExportColumn::make('acknowledgement_status'),
            ExportColumn::make('reminder_count'),
            ExportColumn::make('happy_code'),
            ExportColumn::make('happy_code_delivered_time'),
            ExportColumn::make('acknowledged_by'),
            ExportColumn::make('is_happy_code'),
            ExportColumn::make('is_sms_send'),
//            ExportColumn::make('source'),
            ExportColumn::make('customer.first_name')
                ->label('First Name'),
            ExportColumn::make('customer.last_name')
                ->label('Last Name'),
            ExportColumn::make('customer.email')
                ->label('Email'),
            ExportColumn::make('hospital.hospital_name')
                ->label('Hospital Name'),
            ExportColumn::make('departmentData.name')
                ->label('Department Name'),
            ExportColumn::make('hospital.city')
                ->label('City'),
            ExportColumn::make('hospital.state')
                ->label('State'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your service request export has completed and ' . Number::format($export->successful_rows) . ' ' . str('row')->plural($export->successful_rows) . ' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . Number::format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to export.';
        }

        return $body;
    }
}
