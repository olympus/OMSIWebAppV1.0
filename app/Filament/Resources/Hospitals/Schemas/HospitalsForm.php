<?php

namespace App\Filament\Resources\Hospitals\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class HospitalsForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('hospital_name')
                    ->default(null),
                TextInput::make('dept_id')
                    ->default(null),
                Textarea::make('address')
                    ->default(null)
                    ->columnSpanFull(),
                Textarea::make('city')
                    ->default(null)
                    ->columnSpanFull(),
                Textarea::make('state')
                    ->default(null)
                    ->columnSpanFull(),
                Textarea::make('zip')
                    ->default(null)
                    ->columnSpanFull(),
                Textarea::make('country')
                    ->default(null)
                    ->columnSpanFull(),
                TextInput::make('responsible_branch')
                    ->required()
                    ->default('GURGAON MAIN'),
                TextInput::make('customer_id')
                    ->default(null),
            ]);
    }
}
