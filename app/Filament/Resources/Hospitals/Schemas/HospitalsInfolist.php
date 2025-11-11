<?php

namespace App\Filament\Resources\Hospitals\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class HospitalsInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('hospital_name')
                    ->placeholder('-'),
                TextEntry::make('dept_id')
                    ->placeholder('-'),
                TextEntry::make('address')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('city')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('state')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('zip')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('country')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('responsible_branch'),
                TextEntry::make('customer_id')
                    ->placeholder('-'),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}
