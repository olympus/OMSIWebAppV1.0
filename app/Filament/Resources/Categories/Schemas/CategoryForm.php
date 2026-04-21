<?php

namespace App\Filament\Resources\Categories\Schemas;

use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Schemas\Schema;
use App\Models\Category;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class CategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([

                /*
                |--------------------------------------------------------------------------
                | Name
                |--------------------------------------------------------------------------
                */

                TextInput::make('categories_name')
                    ->label('Name')
                    ->live(onBlur: true)
                    ->afterStateUpdated(function ($state, callable $set) {

                        $trimmed = trim(preg_replace('/\s+/', ' ', $state));

                        $set('categories_name', $trimmed);

                        $set('slug', Str::slug($trimmed));

                    })
                    ->rule('max:75') // strict validation
                    ->required()
                    ->regex('/^[A-Za-z0-9]+( [A-Za-z0-9]+)*$/')
                    ->rule(fn ($record) =>
                        Rule::unique('categories', 'categories_name')
                            ->whereNull('deleted_at')
                            ->ignore($record?->id)
                    ),




                /*
                |--------------------------------------------------------------------------
                | Slug
                |--------------------------------------------------------------------------
                */

                TextInput::make('slug')
                    ->required()
                    ->rule('max:75') // strict validation
                    ->rule('regex:/^[a-z0-9]+(-[a-z0-9]+)*$/')
                    ->rule(fn ($record) =>
                        Rule::unique('categories', 'slug')
                            ->whereNull('deleted_at')
                            ->ignore($record?->id)
                    ),

                /*
                |--------------------------------------------------------------------------
                | Parent
                |--------------------------------------------------------------------------
                */

                Select::make('parent_id')
                    ->options(
                        Category::orderBy('categories_name')
                        ->pluck('categories_name', 'id')
                    )
                    ->searchable()
                    ->nullable()
                    ->hidden(), 

                /*
                |--------------------------------------------------------------------------
                | Image Upload
                |--------------------------------------------------------------------------
                */

                FileUpload::make('categories_image')
                    ->label('Categories Image')
                    ->disk('public')
                    ->directory('category/' . date('FY'))
                    ->image()
                    ->enableOpen()
                    ->enableDownload()
                    ->maxSize(310)
                    ->nullable()
                    // remove image → null save
                    ->dehydrateStateUsing(fn ($state) =>
                        blank($state) ? null : $state
                    )
                    // Required validation
                    ->required(fn (Get $get) =>
                        blank($get('categories_image'))
                        && blank($get('categories_image_url'))
                    )
                    ->validationMessages([
                        'required' =>
                        'Either Image Upload or Image URL is required.',
                    ]),

                /*
                |--------------------------------------------------------------------------
                | Image URL
                |--------------------------------------------------------------------------
                */

                TextInput::make('categories_image_url')
                    ->label('Image URL')
                    ->url()
                    ->nullable()
                    ->dehydrateStateUsing(fn ($state) =>
                        blank($state) ? null : $state
                    )
                    // Required validation
                    ->required(fn (Get $get) =>
                        blank($get('categories_image'))
                        && blank($get('categories_image_url'))
                    )
                    // Prevent both filled
                    ->rule(function (Get $get) {
                        return function (
                            $attribute,
                            $value,
                            $fail
                        ) use ($get) {
                            if (
                                filled($get('categories_image'))
                                && filled($value)
                            ) {
                                $fail(
                                    'Please use either Image Upload OR Image URL.'
                                );
                            }
                        };
                    })
                    ->validationMessages([
                        'required' => 'Either Image Upload or Image URL is required.',
                        'url' => 'Enter valid URL',
                    ]),

                Toggle::make('status')
                    ->default(true)
                    ->dehydrateStateUsing(fn ($state) => $state ? 1 : 0), 

                Toggle::make('is_trending')
                    ->default(false)
                    ->dehydrateStateUsing(fn ($state) => $state ? 1 : 0), 

                TextInput::make('orderby')
                    ->label('Order')
                    ->numeric()
                    ->minValue(1)
                    ->default(null)
                    ->validationMessages([
                        'min_value' => 'Order must be greater than 0.',
                    ]), 

                Hidden::make('created_by')
                    ->default(fn ($record) =>
                        $record?->created_by ?? auth()->id()
                    ),

            ]);
    }
}
