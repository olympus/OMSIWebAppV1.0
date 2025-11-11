<?php

namespace App\Filament\Resources\MergedServiceRequests\Schemas;

use App\Models\Departments;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class MergedServiceRequestInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('id')
                    ->label('ID')
                    ->numeric(),
//                TextEntry::make('cvm_id')
//                    ->placeholder('-'),
//                TextEntry::make('import_id')
//                    ->placeholder('-'),
                TextEntry::make('request_type')
                    ->placeholder('-'),
                TextEntry::make('sub_type')
                    ->placeholder('-'),
                TextEntry::make('customer_id')
                    ->placeholder('-'),
                TextEntry::make('hospital_id')
                    ->placeholder('-'),
                TextEntry::make('dept_id')
                    ->placeholder('-'),
                TextEntry::make('remarks')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('closure_remarks')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('sap_id')
                    ->placeholder('-'),
                TextEntry::make('sfdc_id')
                    ->placeholder('-'),
                TextEntry::make('sfdc_customer_id')
                    ->placeholder('-'),
                TextEntry::make('product_category')
                    ->placeholder('-'),
                TextEntry::make('employee_code')
                    ->placeholder('-'),
//                TextEntry::make('last_updated_by')
//                    ->placeholder('-'),
                TextEntry::make('status')
                    ->placeholder('-'),
                TextEntry::make('is_escalated')
                    ->numeric(),
                TextEntry::make('escalation_count')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('escalation_assign1')
                    ->placeholder('-'),
                TextEntry::make('escalation_assign2')
                    ->placeholder('-'),
                TextEntry::make('escalation_assign3')
                    ->placeholder('-'),
                TextEntry::make('escalation_assign4')
                    ->placeholder('-'),
                TextEntry::make('escalation_reasons')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('escalation_remarks')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('escalated_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('feedback_id')
                    ->placeholder('-'),
                TextEntry::make('feedback_requested')
                    ->numeric(),
                TextEntry::make('is_practice')
                    ->numeric(),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
//                TextEntry::make('acknowledgement_updated_at')
//                    ->dateTime()
//                    ->placeholder('-'),
//                TextEntry::make('acknowledgement_status')
//                    ->placeholder('-'),
//                TextEntry::make('reminder_count')
//                    ->numeric()
//                    ->placeholder('-'),
//                TextEntry::make('happy_code')
//                    ->numeric()
//                    ->placeholder('-'),
//                TextEntry::make('happy_code_delivered_time')
//                    ->dateTime()
//                    ->placeholder('-'),
//                TextEntry::make('acknowledged_by')
//                    ->placeholder('-'),
//                TextEntry::make('is_happy_code'),
//                TextEntry::make('is_sms_send'),
                TextEntry::make('source'),


                Section::make('Customer Details')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('customer.id')->label('Customer ID')
                            ->url(fn ($record) => route('filament.admin.resources.customers.view', $record->customer_id))
                            ->openUrlInNewTab() // optional
                            ->color('primary') // makes it look like a link
                            ->icon('heroicon-o-building-office'), // optional,
                        TextEntry::make('customer.title')->label('Salutation'),
                        TextEntry::make('customer.first_name'),
                        TextEntry::make('customer.middle_name'),
                        TextEntry::make('customer.last_name'),
                        TextEntry::make('customer.email'),
                        TextEntry::make('customer.mobile_number'),
                    ]),

                Section::make('Hospital Details')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('hospital.hospital_name')
                            ->label('Hospital Name')
                            ->url(fn ($record) => route('filament.admin.resources.hospitals.view', $record->hospital_id))
                            ->openUrlInNewTab() // optional
                            ->color('primary') // makes it look like a link
                            ->icon('heroicon-o-building-office'), // optional
                        TextEntry::make('departments')
                            ->label('Departments')
                            ->state(function ($record) {
                                $hospital = \App\Models\Hospitals::find($record->hospital_id);

                                if (! $hospital || empty($hospital->dept_id)) {
                                    return '-';
                                }

                                $hospDepts = explode(',', $hospital->dept_id);

                                $departments = Departments::whereIn('id', $hospDepts)
                                    ->pluck('name')
                                    ->toArray();

                                return implode(', ', $departments);
                            }),
                        TextEntry::make('hospital.city'),
                        TextEntry::make('hospital.state'),
                        TextEntry::make('hospital.address'),
                    ]),

                Section::make('Department Details')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('departmentData.id'),
                        TextEntry::make('departmentData.name'),
                    ]),

                Section::make('Employee Details')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('employeeData.id')->label('Employee ID'),
                        TextEntry::make('employeeData.name')->label('Name'),
                        TextEntry::make('employeeData.email')->label('Email'),
                        TextEntry::make('employeeData.designation')->label('Designation'),
                        TextEntry::make('employeeData.employee_code')->label('Employee Code'),
                        TextEntry::make('employeeData.mobile')->label('Mobile'),
                        ImageEntry::make('employeeData.image')
                            ->label('Image')
                            ->disk('public')
                            ->width(100),
                    ]),

            ]);
    }
}
