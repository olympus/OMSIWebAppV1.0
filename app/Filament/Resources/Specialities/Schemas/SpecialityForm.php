<?php

namespace App\Filament\Resources\Specialities\Schemas;

use App\Models\Speciality;
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

class SpecialityForm
{  
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('specialities_name')
                    ->label('Name')
                    ->live(onBlur: true)
                    ->afterStateUpdated(function ($state, callable $set) {
                        $trimmed = trim(preg_replace('/\s+/', ' ', $state));
                        $set('specialities_name', $trimmed);
                        $set('slug', Str::slug($trimmed));
                    })
                    ->required()
                    ->regex('/^[A-Za-z0-9]+( [A-Za-z0-9]+)*$/')
                    ->rule(fn ($record) =>
                        Rule::unique('specialities', 'specialities_name')
                            ->whereNull('deleted_at')
                            ->ignore($record?->id)
                    )
                    ->rule('max:75') // strict validation

                    ->validationMessages([
                        'required' => 'Speciality name is required.',
                        'regex' => 'No leading/trailing spaces and only single space between words allowed.',
                        'unique' => 'This speciality name already exists.',
                    ]),

                TextInput::make('slug')
                    ->label('Slug')
                    ->required()
                    ->rule('regex:/^[a-z0-9]+(-[a-z0-9]+)*$/')
                    ->rule(fn ($record) =>
                        Rule::unique('specialities', 'slug')
                            ->whereNull('deleted_at')
                            ->ignore($record?->id)
                    )
                    ->rule('max:75') // strict validation
                    
                    ->validationMessages([
                        'regex' => 'Slug format is invalid.',
                        'unique' => 'This slug already exists.',
                    ]),

                Select::make('parent_id')
                    ->label('Parent Speciality')
                    ->options(function () {
                        return Speciality::orderBy('specialities_name')
                            ->pluck('specialities_name', 'id')
                            ->toArray();
                    })
                    ->searchable()
                    ->nullable()
                    ->hidden(),

                /*
                |--------------------------------------------------------------------------
                | Image Upload
                |--------------------------------------------------------------------------
                */

                FileUpload::make('specialities_image')
                    ->label('Speciality Image')
                    ->disk('public')
                    ->directory('speciality/' . date('FY'))
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
                        blank($get('specialities_image'))
                        && blank($get('specialities_image_url'))
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

                TextInput::make('specialities_image_url')
                    ->label('Image URL')
                    ->url()
                    ->nullable()
                    ->dehydrateStateUsing(fn ($state) =>
                        blank($state) ? null : $state
                    )
                    // Required validation
                    ->required(fn (Get $get) =>
                        blank($get('specialities_image'))
                        && blank($get('specialities_image_url'))
                    )
                    // Prevent both filled
                    ->rule(function (Get $get) {
                        return function (
                            $attribute,
                            $value,
                            $fail
                        ) use ($get) {
                            if (
                                filled($get('specialities_image'))
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
                    ->dehydrateStateUsing(fn ($state) => $state ? 1 : 0)
                    ->default(true),

                Toggle::make('is_trending') 
                    ->dehydrateStateUsing(fn ($state) => $state ? 1 : 0)
                    ->default(false),

                TextInput::make('orderby')
                    ->label('Order')
                    ->numeric()
                    ->minValue(1)
                    ->default(null)
                    ->validationMessages([
                        'min_value' => 'Order must be greater than 0.',
                    ]), 

                Hidden::make('created_by')
                    ->default(fn ($record) => $record?->created_by ?? auth()->id()),
            ]);
    }


}
