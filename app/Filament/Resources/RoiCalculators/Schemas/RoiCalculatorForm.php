<?php

namespace App\Filament\Resources\RoiCalculators\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class RoiCalculatorForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('customer_id')
                    ->numeric()
                    ->default(null),
                TextInput::make('name')
                    ->default(null),
                TextInput::make('email')
                    ->label('Email address')
                    ->email()
                    ->default(null),
                TextInput::make('mobile')
                    ->default(null),
                TextInput::make('hospital_name')
                    ->default(null),
                TextInput::make('speciality')
                    ->default(null),
                TextInput::make('state')
                    ->default(null),
                TextInput::make('city')
                    ->default(null),
                TextInput::make('pincode')
                    ->default(null),
                TextInput::make('customer_status')
                    ->default(null),
                TextInput::make('processor_profile')
                    ->default(null),
                TextInput::make('endoscopy_suite')
                    ->default(null),
                TextInput::make('procedure_performer')
                    ->default(null),
                TextInput::make('procedures_performed')
                    ->default(null),
            ]);
    }
}
