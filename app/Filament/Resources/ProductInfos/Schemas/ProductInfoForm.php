<?php

namespace App\Filament\Resources\ProductInfos\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class ProductInfoForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('service_requests_id')
                    ->required()
                    ->numeric(),
                Textarea::make('pd_name')
                    ->required()
                    ->columnSpanFull(),
                Textarea::make('pd_serial')
                    ->required()
                    ->columnSpanFull(),
                Textarea::make('pd_description')
                    ->default(null)
                    ->columnSpanFull(),
            ]);
    }
}
