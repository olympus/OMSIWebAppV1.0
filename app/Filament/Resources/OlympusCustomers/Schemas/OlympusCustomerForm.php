<?php

namespace App\Filament\Resources\OlympusCustomers\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class OlympusCustomerForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                // TextInput::make('customer_id')
                //     ->default(null),
                // TextInput::make('sap_customer_id')
                //     ->default(null),
                // TextInput::make('title')
                //     ->default(null),
                TextInput::make('first_name')
                    ->default(null),
                // TextInput::make('middle_name')
                //     ->default(null),
                TextInput::make('last_name')
                    ->default(null),
                TextInput::make('mobile_number')
                    ->default(null),
                TextInput::make('email')
                    ->label('Email address')
                    ->email()
                    ->default(null),
                // TextInput::make('otp_code')
                //     ->numeric()
                //     ->default(null),
                // DateTimePicker::make('valid_upto'),
                Toggle::make('is_verified')
                    ->required()->label('OTP Verification Status'),
                // Toggle::make('is_testing')
                //     ->required(),
                TextInput::make('password')
                    ->rules([
                        'required',
                        'string',
                        'min:8',
                        'regex:/[a-z]/',
                        'regex:/[A-Z]/',
                        'regex:/[0-9]/',
                        'regex:/[#?!@$%^&*-]/'
                    ])
                    ->default(null)
                    ->password()
                    ->columnSpanFull()
                    ->helperText(new \Illuminate\Support\HtmlString(
                        '<ul style="margin-left: 15px; list-style-type: disc;">
                            <li><b>Password should be a minimum of 8 characters.</b></li>
                            <li><b>You cannot use any white space in the password.</b></li>
                            <li><b>Password should not contain 3 sequential alphabetic characters</b> (e.g., abc, bcd, etc.).</li>
                            <li><b>You cannot use your name or email in the password.</b></li>
                            <li><b>You cannot reuse any of your last 5 passwords.</b></li>
                        </ul>'
                    )),
                // TextInput::make('hospital_id')
                //     ->default(null),
                // TextInput::make('platform')
                //     ->default(null),
                // TextInput::make('app_version')
                //     ->default(null),
                // Toggle::make('is_expired')
                //     ->required(),
                // DateTimePicker::make('password_updated_at'),
                // Toggle::make('is_deleted')
                //     ->required(),
                // TextInput::make('old_password')
                //     ->password()
                //     ->default(null),
                // TextInput::make('is_password_changed')
                //     ->password()
                //     ->required()
                //     ->default('0'),
            ]);
    }
}
