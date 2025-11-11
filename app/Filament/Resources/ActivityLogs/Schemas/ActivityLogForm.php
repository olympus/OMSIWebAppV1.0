<?php

namespace App\Filament\Resources\ActivityLogs\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class ActivityLogForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('log_name')
                    ->default(null),
                Textarea::make('description')
                    ->required()
                    ->columnSpanFull(),
                TextInput::make('subject_id')
                    ->numeric()
                    ->default(null),
                TextInput::make('subject_type')
                    ->default(null),
                TextInput::make('causer_id')
                    ->numeric()
                    ->default(null),
                TextInput::make('causer_type')
                    ->default(null),
                Textarea::make('properties')
                    ->default(null)
                    ->columnSpanFull(),
            ]);
    }
}
