<?php

namespace App\Filament\Resources\Specialities\Schemas;

use App\Models\Speciality;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\IconEntry;
use Filament\Support\Icons\Heroicon;
use App\Models\User;
use Filament\Schemas\Schema;

class SpecialityInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            /*->components([
                TextEntry::make('specialities_name')
                    ->placeholder('-'),
                ImageEntry::make('specialities_image')
                    ->placeholder('-'),
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
                    ->visible(fn (Speciality $record): bool => $record->trashed()),
            ]);*/

            ->components([
                TextEntry::make('specialities_name')
                    ->label('Name')
                    ->placeholder('-'),

                TextEntry::make('slug')
                    ->label('Slug')
                    ->placeholder('-'),

                TextEntry::make('parent_id')
                    ->label('Parent Speciality')
                    ->formatStateUsing(fn ($state) =>
                        optional(Speciality::find($state))->specialities_name ?? '-'
                    )
                    ->placeholder('-'),

                ImageEntry::make('specialities_image')
                    ->label('Image')
                    ->placeholder('-'),

                TextEntry::make('specialities_image_url')
                    ->label('Image URL')
                    ->url(fn ($state) => $state)
                    ->openUrlInNewTab()
                    ->placeholder('-'),

                IconEntry::make('status')
                    ->label('Status')
                    ->boolean()
                    ->true(Heroicon::OutlinedCheckCircle, 'success')
                    ->false(Heroicon::OutlinedXCircle, 'danger'),

                IconEntry::make('is_trending')
                    ->label('Is Trending')
                    ->boolean()
                    ->true(Heroicon::OutlinedCheckCircle, 'success')
                    ->false(Heroicon::OutlinedXCircle, 'danger'),

                TextEntry::make('orderby')
                    ->numeric()
                    ->placeholder('-'),

                TextEntry::make('created_by')
                    ->label('Created By')
                    ->formatStateUsing(fn ($state) =>
                        optional(User::find($state))->name ?? '-'
                    )
                    ->placeholder('-'),

                TextEntry::make('created_at')
                    ->label('Created At')
                    ->dateTime()
                    ->placeholder('-'),

                TextEntry::make('updated_at')
                    ->label('Updated At')
                    ->dateTime()
                    ->placeholder('-'),

                TextEntry::make('deleted_at')
                    ->label('Deleted At')
                    ->dateTime()
                    ->visible(fn (Speciality $record): bool => $record->trashed()),
            ]);
    }
}
