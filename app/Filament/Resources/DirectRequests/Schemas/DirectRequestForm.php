<?php

namespace App\Filament\Resources\DirectRequests\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class DirectRequestForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('sap_id')
                    ->numeric()
                    ->default(null),
                Textarea::make('status')
                    ->default(null)
                    ->columnSpanFull(),
                Textarea::make('fse_code')
                    ->default(null)
                    ->columnSpanFull(),
                Textarea::make('customer_name')
                    ->default(null)
                    ->columnSpanFull(),
                Textarea::make('customer_code')
                    ->default(null)
                    ->columnSpanFull(),
                Textarea::make('customer_city')
                    ->default(null)
                    ->columnSpanFull(),
                Textarea::make('customer_state')
                    ->default(null)
                    ->columnSpanFull(),
                Textarea::make('prod_model_no')
                    ->default(null)
                    ->columnSpanFull(),
                Textarea::make('prod_material')
                    ->default(null)
                    ->columnSpanFull(),
                Textarea::make('prod_serial_no')
                    ->default(null)
                    ->columnSpanFull(),
                Textarea::make('prod_equipment_no')
                    ->default(null)
                    ->columnSpanFull(),
                Textarea::make('prod_material_description')
                    ->default(null)
                    ->columnSpanFull(),
                Textarea::make('sort_field')
                    ->default(null)
                    ->columnSpanFull(),
                Textarea::make('contract_desc')
                    ->default(null)
                    ->columnSpanFull(),
                Textarea::make('branch')
                    ->default(null)
                    ->columnSpanFull(),
                Textarea::make('zone')
                    ->default(null)
                    ->columnSpanFull(),
            ]);
    }
}
