<?php

namespace App\Filament\Resources\ServiceMasters\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class ServiceMasterInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('request_type')
                    ->placeholder('-'),
                TextEntry::make('sub_type')
                    ->placeholder('-'),
                TextEntry::make('states')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('departments')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('to_emails')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('cc_emails')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('escalation_1')
                    ->columnSpanFull(),
                TextEntry::make('escalation_2')
                    ->columnSpanFull(),
                TextEntry::make('escalation_3')
                    ->columnSpanFull(),
                TextEntry::make('escalation_4')
                    ->columnSpanFull(),
                TextEntry::make('created_at')
                    ->dateTime(),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}
