<?php

namespace App\Filament\Resources\EmployeeTeams\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class EmployeeTeamInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('name')
                    ->placeholder('-'),
                TextEntry::make('email')
                    ->label('Email address')
                    ->placeholder('-'),
                TextEntry::make('designation')
                    ->placeholder('-'),
                TextEntry::make('employee_code')
                    ->placeholder('-'),
                IconEntry::make('disabled')
                    ->boolean(),
                ImageEntry::make('image')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('mobile')
                    ->placeholder('-'),
                TextEntry::make('gender')
                    ->placeholder('-'),
                TextEntry::make('category')
                    ->placeholder('-'),
                TextEntry::make('branch')
                    ->placeholder('-'),
                TextEntry::make('zone')
                    ->placeholder('-'),
                TextEntry::make('escalation_1')
                    ->placeholder('-'),
                TextEntry::make('escalation_2')
                    ->placeholder('-'),
                TextEntry::make('escalation_3')
                    ->placeholder('-'),
                TextEntry::make('escalation_4')
                    ->placeholder('-'),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}
