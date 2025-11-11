<?php

namespace App\Filament\Resources\ApiRequests\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class ApiRequestsInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('identifier')
                    ->columnSpanFull(),
                TextEntry::make('request_type')
                    ->columnSpanFull(),
                TextEntry::make('request_url')
                    ->columnSpanFull(),
                TextEntry::make('request_body')
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
