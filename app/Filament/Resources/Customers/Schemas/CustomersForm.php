<?php

namespace App\Filament\Resources\Customers\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class CustomersForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([ 
                TextInput::make('first_name')->default(null),
                
                TextInput::make('last_name')->default(null),
                
                TextInput::make('mobile_number')->default(null),
                
                TextInput::make('email')->label('Email address')->email()->default(null),
                
                Toggle::make('is_verified')->required()->label('OTP Verification Status'),
                
                TextInput::make('password')
                    ->rules([
                        'nullable',
                        'string',
                        'min:8',
                        'regex:/[a-z]/',
                        'regex:/[A-Z]/',
                        'regex:/[0-9]/',
                        'regex:/[#?!@$%^&*-]/'
                    ])
                    ->password()
                    ->columnSpanFull()
                    ->dehydrated(fn ($state) => filled($state)) // ⭐ important
                    ->helperText(new \Illuminate\Support\HtmlString(
                        '<ul style="margin-left: 15px; list-style-type: disc;">
                            <li><b>Password should be a minimum of 8 characters.</b></li>
                            <li><b>You cannot use any white space in the password.</b></li>
                            <li><b>Password should not contain 3 sequential alphabetic characters</b> (e.g., abc, bcd, etc.).</li>
                            <li><b>You cannot use your name or email in the password.</b></li>
                            <li><b>You cannot reuse any of your last 5 passwords.</b></li>
                        </ul>'
                    )),


                Toggle::make('is_account_block'), 
            ]);
    }
}
