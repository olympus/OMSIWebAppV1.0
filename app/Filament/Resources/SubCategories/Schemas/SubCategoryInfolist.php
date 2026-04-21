<?php

namespace App\Filament\Resources\SubCategories\Schemas;

use App\Models\SubCategory;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\IconEntry;
use Filament\Support\Icons\Heroicon;
use App\Models\User;
use Filament\Schemas\Schema;

class SubCategoryInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('category.categories_name')
                    ->label('Category')
                    ->placeholder('-'),
                TextEntry::make('sub_categories_name')
                    ->placeholder('-'),
                ImageEntry::make('sub_categories_image')
                    ->placeholder('-'),
                TextEntry::make('sub_categories_description')
                    ->placeholder('-')
                    ->columnSpanFull(),
                IconEntry::make('status')
                    ->label('Status')
                    ->boolean()
                    ->true(Heroicon::OutlinedCheckCircle, 'success')
                    ->false(Heroicon::OutlinedXCircle, 'danger'),
                TextEntry::make('orderby')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('created_by')
                    ->label('Created By')
                    ->formatStateUsing(fn ($state) => optional(User::find($state))->name ?? '-')->placeholder('-'),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('deleted_at')
                    ->dateTime()
                    ->visible(fn (SubCategory $record): bool => $record->trashed()),
            ]);
    }
}
