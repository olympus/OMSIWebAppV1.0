<?php

namespace App\Filament\Resources\EnquiryRequestData\Schemas;

use App\Models\EmployeeTeam;
use App\Models\Customers;
use App\Models\Hospitals;
use App\Models\Departments;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
// <-- IMPORTANT: import the Schemas Get utility
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Forms\Components\Hidden;

class EnquiryRequestDataForm
{
    public static function configure(Schema $schema): Schema
    {
        // Define your static mapping (like your Blade PHP array)
        $requestData = [
            'service'      => ['BreakDown Call', 'Breakdown Call', 'Service Support'],
            'academic'     => ['Conference', 'Clinical Info', 'Training'],
            'enquiry'      => ['Demonstration', 'Quotation', 'Quotations', 'Product Info'],
            'installation' => [],
        ];

        return $schema
            ->components([
                TextInput::make('cvm_id')
                    ->label('My Voice Id')
                    ->default(null)
                    ->disabled()->columnSpanFull(),

                // ✅ Request Type (dynamic select or readonly)
                Select::make('request_type')
                    ->label('Request Type')
                    ->options([
                        'service'      => 'Service',
                        'academic'     => 'Academic',
                        'enquiry'      => 'Enquiry',
                        //'installation' => 'Installation',
                    ])
                    // only editable when status is Received
                    ->disabled(fn (Get $get) => $get('status') !== 'Received')
                    ->required()
                    ->reactive()
                    // clear sub_type when request_type changes
                    ->afterStateUpdated(function (Get $get, callable $set) {
                        $set('sub_type', null);
                    })->columnSpanFull(),

                    Select::make('sub_type')
                        ->label('Sub Type')
                        ->options(function (Get $get) use ($requestData) {
                            $type = $get('request_type');
                            $list = $requestData[$type] ?? [];
                            // Filament expects key => label pairs
                            return collect($list)->mapWithKeys(fn ($v) => [$v => $v])->toArray();
                        })
                        ->disabled(fn (Get $get) => $get('status') !== 'Received')
                        ->reactive()
                        ->placeholder('Select Sub Type')->columnSpanFull(),

                    Select::make('customer_id') // actual foreign key field
                        ->label('Customer Name')
                        ->options(
                            Customers::where('is_expired', 0)
                                ->where('is_deleted', 0)
                                ->orderBy('id', 'ASC')
                                ->get()
                                ->mapWithKeys(function ($cust) {
                                    return [
                                        $cust->id => ucfirst($cust->first_name) . ' ' . ucfirst($cust->last_name),
                                    ];
                                })
                        )
                        ->searchable()
                        ->preload()
                        ->columnSpanFull()
                        ->default(function ($record) {
                            return $record ? $record->customer_id : null;
                        }),

                    Select::make('hospital_id') // actual foreign key field
                        ->label('Hospital Name')
                        ->options(
                            Hospitals::orderBy('id', 'ASC')
                                ->get()
                                ->mapWithKeys(function ($hosp) {
                                    return [
                                        $hosp->id => ucfirst($hosp->hospital_name),
                                    ];
                                })
                        )
                        ->searchable()
                        ->preload()
                        ->columnSpanFull()
                        ->default(function ($record) {
                            return $record ? $record->hospital_id : null;
                        }),

                    Select::make('dept_id') // actual foreign key field
                        ->label('Department Name')
                        ->options(
                            Departments::orderBy('sort_order', 'ASC')
                                ->get()
                                ->mapWithKeys(function ($cust) {
                                    return [
                                        $cust->id => ucfirst($cust->name),
                                    ];
                                })
                        )
                        ->searchable()
                        ->preload()
                        ->columnSpanFull()
                        ->default(function ($record) {
                            return $record ? $record->dept_id : null;
                        }),
                 
                    
                TextInput::make('remarks')
                    ->default(null)
                    ->label('Customer Remarks')
                    ->disabled()
                    ->columnSpanFull(),

                TextInput::make('sfdc_id')
                    ->label('Sales Force Lead ID')
                    ->default(null)
                    ->disabled()
                    ->columnSpanFull(),

                TextInput::make('sfdc_customer_id')
                    ->label('Sales Force Customer ID')
                    ->default(null)
                    ->columnSpanFull(),

                Select::make('employee_code') // actual foreign key field
                    ->label('Employee Code')
                    ->options(
                        EmployeeTeam::where('disabled', '0')
                            ->orderBy('id', 'ASC')
                            ->get()
                            ->mapWithKeys(function ($engg) {
                                return [
                                    $engg->employee_code => ucfirst($engg->name) . ' | ' . $engg->employee_code . ' | ' . ucfirst($engg->category),
                                ];
                            })
                    )
                    ->searchable()
                    ->preload()
                    ->columnSpanFull()
                    ->default(function ($record) {
                        return $record ? $record->employee_code : null;
                    }),

                Select::make('status')
                ->label('Request Status') // base label
                ->options([
                    'Received' => 'Received',
                    'Assigned' => 'Assigned',
                    'Attended' => 'Attended',
                    'Re-assigned' => 'Re Assigned', 
                    'Closed' => 'Closed', 
                ])
                ->default(fn ($record) => $record ? $record->status : null)
                ->columnSpanFull()
                ->native(false)
                ->placeholder('Select Status')
                ->required()
                ->afterStateHydrated(function ($component, $state, $record) {
                    // Dynamically update label to show current status
                    $component->label('Request Status (Current Status: ' . ($record->status ?? '-') . ')');
                }) 
            ]);
    }
}
