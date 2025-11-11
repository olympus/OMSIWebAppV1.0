<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Filament\Forms\Components\MultiSelect;
use Spatie\Permission\Models\Role;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->rules([
                        'required',
                        'string',
                        'min:2',
                        'max:100',
                        'regex:/[a-zA-Z\s]/'
                    ])
                    ->required(),
                // TextInput::make('email')
                //     ->label('Email address')
                //     ->email()
                //     ->required(),
                MultiSelect::make('roles')
                    ->label('Roles')
                    ->options(Role::pluck('name', 'id')->toArray())
                    ->relationship('roles', 'name') // Pre-selects already assigned roles
                    ->required(),
                TextInput::make('password')
                    ->rules([
                        'required',
                        'string',
                        'min:20',
                        'regex:/[a-z]/',
                        'regex:/[A-Z]/',
                        'regex:/[0-9]/',
                        'regex:/[#?!@$%^&*-]/'
                    ])
                    ->required()
                    ->password()
                    ->columnSpanFull()
                    ->helperText(new \Illuminate\Support\HtmlString(
                        '<ul style="margin-left: 15px; list-style-type: disc;">
                            <li><b>Password should be a minimum of 20 characters.</b></li>
                            <li><b>You cannot use any white space in the password.</b></li>
                            <li><b>Password should not contain 3 sequential alphabetic characters</b> (e.g., abc, bcd, etc.).</li>
                            <li><b>You cannot use your name or email in the password.</b></li>
                            <li><b>You cannot reuse any of your last 5 passwords.</b></li>
                        </ul>'
                    )),
                // Toggle::make('is_expired')
                //     ->required(),
                // DateTimePicker::make('password_updated_at'),
            ]);
    }
}
