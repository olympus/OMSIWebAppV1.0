<?php

namespace App\Filament\Resources\AutoEmails\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class AutoEmailsForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('request_type')
                    ->default(null),
                TextInput::make('sub_type')
                    ->default(null),
                Textarea::make('states')
                    ->default(null)
                    ->columnSpanFull(),
                Textarea::make('departments')
                    ->default(null)
                    ->columnSpanFull(),
                Textarea::make('to_emails')
                    ->default(null)
                    ->columnSpanFull(),
                Textarea::make('cc_emails')
                    ->default(null)
                    ->columnSpanFull(),
                Textarea::make('escalation_1')
                    ->required()
                    ->columnSpanFull(),
                Textarea::make('escalation_2')
                    ->required()
                    ->columnSpanFull(),
                Textarea::make('escalation_3')
                    ->required()
                    ->columnSpanFull(),
                Textarea::make('escalation_4')
                    ->required()
                    ->columnSpanFull(),
            ]);
    }
}
