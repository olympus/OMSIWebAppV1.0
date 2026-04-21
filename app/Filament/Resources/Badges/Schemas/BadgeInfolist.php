<?php

namespace App\Filament\Resources\Badges\Schemas;

use App\Models\Badge;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class BadgeInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('name')
                    ->placeholder('-'),
                TextEntry::make('icon')
                    ->placeholder('-'),
                TextEntry::make('description')
                    ->placeholder('-'),
                TextEntry::make('order_by')
                    ->numeric()
                    ->placeholder('-'),
                IconEntry::make('status')
                    ->boolean(),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('deleted_at')
                    ->dateTime()
                    ->visible(fn (Badge $record): bool => $record->trashed()),
            ]);
    }
}
