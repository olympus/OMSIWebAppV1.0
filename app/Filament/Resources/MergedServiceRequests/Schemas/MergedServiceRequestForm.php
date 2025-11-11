<?php

namespace App\Filament\Resources\MergedServiceRequests\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class MergedServiceRequestForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('cvm_id')->label('My Voice Id')
                    ->default(null)->disabled(),
//                TextInput::make('import_id')
//                    ->default(null),
                TextInput::make('request_type')
                    ->default(null),
                TextInput::make('sub_type')
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
                Textarea::make('closure_remarks')
                    ->default(null)
                    ->columnSpanFull(),
                TextInput::make('sap_id')
                    ->default(null),
                TextInput::make('sfdc_id')
                    ->default(null),
                TextInput::make('sfdc_customer_id')
                    ->default(null),
                TextInput::make('product_category')
                    ->default(null),
                TextInput::make('employee_code')
                    ->default(null),
                TextInput::make('last_updated_by')
                    ->default(null),
                TextInput::make('status')
                    ->default(null),
                TextInput::make('is_escalated')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('escalation_count')
                    ->numeric()
                    ->default(null),
                TextInput::make('escalation_assign1')
                    ->default(null),
                TextInput::make('escalation_assign2')
                    ->default(null),
                TextInput::make('escalation_assign3')
                    ->default(null),
                TextInput::make('escalation_assign4')
                    ->default(null),
                Textarea::make('escalation_reasons')
                    ->default(null)
                    ->columnSpanFull(),
                Textarea::make('escalation_remarks')
                    ->default(null)
                    ->columnSpanFull(),
                DateTimePicker::make('escalated_at'),
                TextInput::make('feedback_id')
                    ->default(null),
                TextInput::make('feedback_requested')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('is_practice')
                    ->required()
                    ->numeric()
                    ->default(0),
                DateTimePicker::make('acknowledgement_updated_at'),
                TextInput::make('acknowledgement_status')
                    ->default(null),
                TextInput::make('reminder_count')
                    ->numeric()
                    ->default(null),
                TextInput::make('happy_code')
                    ->numeric()
                    ->default(null),
                DateTimePicker::make('happy_code_delivered_time'),
                TextInput::make('acknowledged_by')
                    ->default(null),
                TextInput::make('is_happy_code')
                    ->required()
                    ->default(''),
                TextInput::make('is_sms_send')
                    ->required()
                    ->default(''),
                TextInput::make('source')
                    ->required()
                    ->default(''),
            ]);
    }
}
