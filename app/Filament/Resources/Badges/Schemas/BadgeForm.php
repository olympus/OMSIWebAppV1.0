<?php

namespace App\Filament\Resources\Badges\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class BadgeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->default(null),
                FileUpload::make('icon')
                    ->image()
                    ->default(null),
                TextInput::make('description')
                    ->default(null),
                TextInput::make('order_by')
                    ->numeric()
                    ->default(null),
                Toggle::make('status')
                    ->default(true)
                    ->required(),
            ]);
    }
}
