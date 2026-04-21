<?php

namespace App\Filament\Resources\SubCategories\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use App\Models\Category;
use Filament\Schemas\Schema;

class SubCategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('category_id')
                    ->label('Category')
                    ->options(function () {
                        return Category::orderBy('categories_name', 'ASC')
                            ->get()
                            ->mapWithKeys(fn ($cat) => [$cat->id => $cat->categories_name])
                            ->toArray();
                    })
                    ->searchable()
                    ->preload()
                    ->required()
                    ->default(function ($record) {
                        return $record ? $record->category_id : null;
                    }),
                TextInput::make('sub_categories_name')
                    ->default(null),
                FileUpload::make('sub_categories_image')
                    ->image(),
                Textarea::make('sub_categories_description')
                    ->default(null)
                    ->columnSpanFull(),
                Toggle::make('status')
                    ->label('Status')
                    ->required()
                    ->dehydrateStateUsing(fn ($state) => $state ? 1 : 0)
                    ->default(true),
                TextInput::make('orderby')
                    ->numeric()
                    ->default(null),
                Hidden::make('created_by')
                    ->default(function ($record) {
                        return $record ? $record->created_by : auth()->id();
                    }),
            ]);
    }
}
