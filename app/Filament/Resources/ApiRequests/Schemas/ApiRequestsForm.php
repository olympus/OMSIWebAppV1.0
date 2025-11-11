<?php

namespace App\Filament\Resources\ApiRequests\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class ApiRequestsForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Textarea::make('identifier')
                    ->required()
                    ->columnSpanFull(),
                Textarea::make('request_type')
                    ->required()
                    ->columnSpanFull(),
                Textarea::make('request_url')
                    ->required()
                    ->columnSpanFull(),
                Textarea::make('request_body')
                    ->required()
                    ->columnSpanFull(),
            ]);
    }
}
