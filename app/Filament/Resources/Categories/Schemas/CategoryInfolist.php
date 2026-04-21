<?php

namespace App\Filament\Resources\Categories\Schemas;

use App\Models\Category;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\IconEntry;
use Filament\Support\Icons\Heroicon;
use App\Models\User;
use Filament\Schemas\Schema;

class CategoryInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('categories_name')
                    ->label('Name')
                    ->placeholder('-'),

                TextEntry::make('slug')
                    ->label('Slug')
                    ->placeholder('-'),

                TextEntry::make('parent_id')
                    ->label('Parent Category')
                    ->formatStateUsing(fn ($state) =>
                        optional(Category::find($state))->categories_name ?? '-'
                    )
                    ->placeholder('-'),

                ImageEntry::make('categories_image')
                    ->label('Image')
                    ->placeholder('-'),

                TextEntry::make('categories_image_url')
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
                    ->visible(fn (Category $record): bool => $record->trashed()),
            ]);
            
            //     TextEntry::make('categories_name')
            //         ->placeholder('-')
            //         ->label('Name'),
            //     ImageEntry::make('categories_image')
            //         ->placeholder('-')
            //         ->label('Image'),
            //     IconEntry::make('status')
            //         ->label('Status')
            //         ->boolean()
            //         ->true(Heroicon::OutlinedCheckCircle, 'success')
            //         ->false(Heroicon::OutlinedXCircle, 'danger'),
            //     TextEntry::make('orderby')
            //         ->numeric()
            //         ->placeholder('-'),
            //     TextEntry::make('created_by')
            //         ->label('Created By')
            //         ->formatStateUsing(fn ($state) => optional(User::find($state))->name ?? '-')->placeholder('-'),
            //     TextEntry::make('created_at')
            //         ->dateTime()
            //         ->placeholder('-'),
            //     TextEntry::make('updated_at')
            //         ->dateTime()
            //         ->placeholder('-'),
            //     TextEntry::make('deleted_at')
            //         ->dateTime()
            //         ->visible(fn (Category $record): bool => $record->trashed()),
            // ]);
    }
}
