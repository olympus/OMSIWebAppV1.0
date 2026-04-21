<?php

namespace App\Filament\Resources\SubSpecialities\Schemas;

use App\Models\SubSpeciality;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use App\Models\User;
use Filament\Schemas\Schema;

class SubSpecialityInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('speciality.specialities_name')
                    ->label('Speciality')
                    ->placeholder('-'),
                TextEntry::make('sub_specialities_name')
                    ->placeholder('-'),
                ImageEntry::make('sub_specialities_image')
                    ->placeholder('-'),
                TextEntry::make('sub_specialities_description')
                    ->placeholder('-')
                    ->columnSpanFull(),
                IconEntry::make('status')
                    ->boolean(),
                TextEntry::make('orderby')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('created_by')
                    ->label('Created By')
                    ->formatStateUsing(fn ($state) => optional(User::find($state))->name ?? '-')
                    ->placeholder('-'),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('deleted_at')
                    ->dateTime()
                    ->visible(fn (SubSpeciality $record): bool => $record->trashed()),
            ]);
    }
}
