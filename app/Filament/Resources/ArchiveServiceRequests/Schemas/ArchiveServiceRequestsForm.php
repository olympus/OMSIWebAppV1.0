<?php

namespace App\Filament\Resources\ArchiveServiceRequests\Schemas;

use App\Models\EmployeeTeam;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class ArchiveServiceRequestsForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('cvm_id')->label('My Voice Id')
                    ->default(null)->disabled(),
//                TextInput::make('import_id')
//                    ->default(null),
                TextInput::make('request_type')->disabled()
                    ->default(null),
                TextInput::make('sub_type')->disabled()
                    ->default(null),
//                TextInput::make('customer_id')
//                    ->default(null),
//                TextInput::make('hospital_id')
//                    ->default(null),
//                TextInput::make('dept_id')
//                    ->default(null),
//                Textarea::make('remarks')
//                    ->default(null)
//                    ->columnSpanFull(),
                Textarea::make('closure_remarks')->disabled()
                    ->default(null)
                    ->columnSpanFull(),
                TextInput::make('sfdc_id')->label('Sales Force Lead ID')->disabled()
                    ->default(null),
                TextInput::make('sfdc_customer_id')->label('Sales Force Customer ID')
                    ->default(null),
//                TextInput::make('product_category')
//                    ->default(null),
                Select::make('employee_code')
                    ->label('Employee')
                    ->options(
                        EmployeeTeam::where('disabled', '0')
                            ->orderBy('id', 'ASC')
                            ->get()
                            ->mapWithKeys(function ($engg) {
                                return [
                                    $engg->employee_code => "{$engg->name} | {$engg->employee_code} | {$engg->category}",
                                ];
                            })
                    )
                    ->searchable()
                    ->preload(),
                Select::make('status')
                    ->label('Status')
                    ->options([
                        'Received' => 'Received',
                        'Assigned' => 'Assigned',
                        'Attended' => 'Attended',
                        'Re-assigned' => 'Re-assigned',
                        'Closed' => 'Closed',
                    ])
                    ->native(false) // for better Filament-styled dropdown
                    ->placeholder('Select Status')
                    ->required(),
            ]);
    }
}
