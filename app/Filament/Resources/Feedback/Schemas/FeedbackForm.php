<?php

namespace App\Filament\Resources\Feedback\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class FeedbackForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('request_id')
                    ->default(null),
                TextInput::make('response_speed')
                    ->default(null),
                TextInput::make('quality_of_response')
                    ->default(null),
                TextInput::make('app_experience')
                    ->default(null),
                TextInput::make('olympus_staff_performance')
                    ->default(null),
                Textarea::make('remarks')
                    ->default(null)
                    ->columnSpanFull(),
            ]);
    }
}
