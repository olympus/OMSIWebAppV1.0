<?php

namespace App\Filament\Resources\DirectRequests\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class DirectRequestInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('sap_id')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('status')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('fse_code')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('customer_name')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('customer_code')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('customer_city')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('customer_state')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('prod_model_no')
                    ->placeholder('-')
                    ->label('Prod Model No')
                    ->columnSpanFull(),
                TextEntry::make('prod_material')
                    ->placeholder('-')
                    ->label('Prod Material')
                    ->columnSpanFull(),
                TextEntry::make('prod_serial_no')
                    ->placeholder('-')
                    ->label('Prod Serial No')
                    ->columnSpanFull(),
                TextEntry::make('prod_equipment_no')
                    ->placeholder('-')
                    ->label('Prod Equipment No')
                    ->columnSpanFull(),
                TextEntry::make('prod_material_description')
                    ->placeholder('-')
                    ->label('Prod Material Description')
                    ->columnSpanFull(),
                TextEntry::make('sort_field')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('contract_desc')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('branch')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('zone')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}
