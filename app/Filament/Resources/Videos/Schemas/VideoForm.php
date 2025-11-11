<?php

namespace App\Filament\Resources\Videos\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class VideoForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title')
                    ->required(),
                TextInput::make('url')
                    ->url()
                    ->required(),
                Textarea::make('description')
                    ->required()
                    ->columnSpanFull(),
                TextInput::make('nt_title')
                    ->required(),
                TextInput::make('nt_description')
                    ->required(),
                Toggle::make('enabled')
                    ->required(),
            ]);
    }
}
