<?php

namespace App\Filament\Resources\EmployeeTeams\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\FileUpload;

class EmployeeTeamForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->default(null),
                TextInput::make('email')
                    ->label('Email address')
                    ->email()
                    ->default(null),
                TextInput::make('designation')
                    ->default(null),
                TextInput::make('employee_code')
                    ->default(null),
                // Toggle::make('disabled')
                //     ->required(),
                FileUpload::make('image')
                    ->label('Employee Image')
                    ->directory('employeeimages') // store in storage/app/employeeimages
                    ->disk('public') // optional, choose disk (default is "public")
                    ->image() // optional, restrict to images
                    ->columnSpanFull()
                    ->nullable(),

                TextInput::make('mobile')
                    ->default(null),
                Select::make('gender')
                    ->label('Gender')
                    ->options([
                        'Male' => 'Male',
                        'Female' => 'Female',
                    ])
                    ->default(null)
                    ->required(),

                TextInput::make('category')
                    ->default(null),
                TextInput::make('branch')
                    ->default(null),
                TextInput::make('zone')
                    ->default(null),
                TextInput::make('escalation_1')
                    ->default(null),
                TextInput::make('escalation_2')
                    ->default(null),
                TextInput::make('escalation_3')
                    ->default(null),
                TextInput::make('escalation_4')
                    ->default(null),
            ]);
    }
}
