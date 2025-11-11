<?php

namespace App\Filament\Resources\ActivityLogs\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class ActivityLogInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('log_name')
                    ->placeholder('-'),
                TextEntry::make('description')
                    ->columnSpanFull(),
                TextEntry::make('subject_id')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('subject_type')
                    ->placeholder('-'),
                TextEntry::make('causer_id')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('causer_type')
                    ->placeholder('-'),
                TextEntry::make('properties')
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
