<?php

namespace App\Filament\Resources\RoiCalculatorSections\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class RoiCalculatorSectionInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('title')
                    ->placeholder('-'),
                TextEntry::make('description')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('video_type'),
                TextEntry::make('video_url')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('video_file')
                    ->placeholder('-'),
                TextEntry::make('thumbnail')
                    ->placeholder('-'),
                IconEntry::make('status')
                    ->boolean(),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}
