<?php

namespace App\Filament\Resources\ProductInfos\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class ProductInfoInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('id')
                    ->numeric()
                    ->label('ID'),
                TextEntry::make('service_requests_id')
                    ->numeric()->label('Service Request Id'),
                TextEntry::make('pd_name')
                    ->label('name'),
                TextEntry::make('pd_serial')
                    ->label('Serial'),
                TextEntry::make('pd_description')
                    ->placeholder('-')
                    ->label('Description'),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}
