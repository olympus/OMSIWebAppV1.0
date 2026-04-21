<?php

namespace App\Filament\Resources\Products\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Hidden;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;
use App\Models\Product;
use Illuminate\Validation\Rule;
use Filament\Schemas\Components\Utilities\Get;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            
            TextInput::make('product_name')
                ->label('Name')
                ->live(onBlur: true)
                ->afterStateUpdated(function ($state, callable $set, $get) {
                    if (!$state) return;

                    $trimmed = trim(preg_replace('/\s+/', ' ', $state));
                    $set('product_name', $trimmed);

                    // ✅ UNIQUE SLUG GENERATE (ignore deleted)
                    $baseSlug = Str::slug($trimmed);
                    $slug = $baseSlug;
                    $count = 1;
                    while (
                        Product::where('slug', $slug)
                            ->whereNull('deleted_at')
                            ->where('status', 1)
                            ->when($get('id'), fn ($q) =>
                                $q->where('id', '!=', $get('id'))
                            )
                            ->exists()
                    ) {
                        $slug = $baseSlug . '-' . $count++;
                    }
                    $set('slug', $slug);
                })
                ->required()
                ->minLength(2)
                ->maxLength(100)
                ->helperText('Maximum 100 characters are allowed.')
                ->regex('/^[a-zA-Z0-9\-]+( [a-zA-Z0-9\-]+)*$/')
                // ✅ IGNORE DELETED PRODUCTS
                // ->rule(fn ($record) =>
                //     Rule::unique('products', 'product_name')
                //         ->whereNull('deleted_at')
                //         ->ignore($record)
                // )
                ->rule(fn ($record) =>
                    Rule::unique('products', 'product_name')
                        ->where(function ($query) {
                            return $query
                                ->whereNull('deleted_at')
                                ->where('status', 1);
                        })
                        ->ignore($record)
                )
                ->validationMessages([
                    'required' => 'Product name is required.',
                    'max' => 'Product name must be less than 100 characters.',
                    'unique' => 'This product name already exists.',
                ]),



            /*
            |--------------------------------------------------------------------------
            | SLUG
            |--------------------------------------------------------------------------
            */

            TextInput::make('slug')
                ->required()
                ->disabled()
                ->dehydrated()
                ->maxLength(255)
                ->rule(fn ($record) =>
                    Rule::unique('products', 'slug')
                        ->where(function ($query) {
                            return $query
                                ->whereNull('deleted_at')
                                ->where('status', 1);
                        })
                        ->ignore($record)
                )
                // ->rule(fn ($record) =>
                //     Rule::unique('products', 'slug')
                //         ->whereNull('deleted_at')
                //         ->ignore($record)
                // )
                ->validationMessages([
                    'required' => 'Slug required.',
                    'unique' => 'Slug already exists.',
                ]), 

            /*
            |--------------------------------------------------------------------------
            | SKU
            |--------------------------------------------------------------------------
            */

            TextInput::make('product_sku')
                ->label('Product SKU')
                ->required()
                ->maxLength(30)
                ->rule(fn ($record) =>
                    Rule::unique('products', 'product_sku')
                        ->where(function ($query) {
                            return $query
                                ->whereNull('deleted_at')
                                ->where('status', 1);
                        })
                        ->ignore($record)
                )
                // ->rule(fn ($record) =>
                //     Rule::unique('products', 'product_sku')
                //         ->whereNull('deleted_at')
                //         ->ignore($record)
                // )
                ->validationMessages([
                    'required' => 'Product SKU required.',
                    'max' => 'Product SKU must not exceed 30 characters.',
                    'unique' => 'SKU already exists.',
                ]),


            /*
            |--------------------------------------------------------------------------
            | IMAGE URL
            |--------------------------------------------------------------------------
            */

            TextInput::make('product_image_url')
                    ->label('Image URL')
                    ->url()
                    ->nullable()
                    ->dehydrateStateUsing(fn ($state) =>
                        blank($state) ? null : $state
                    )
                    // Required validation
                    ->required(fn (Get $get) =>
                        blank($get('product_image'))
                        && blank($get('product_image_url'))
                    )
                    // Prevent both filled
                    ->rule(function (Get $get) {
                        return function (
                            $attribute,
                            $value,
                            $fail
                        ) use ($get) {
                            if (
                                filled($get('product_image'))
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

            // TextInput::make('product_image_url')
            //     ->label('Image URL')
            //     ->url()
            //     ->rule(fn ($get) => function ($attribute, $value, $fail) use ($get) {
            //         if (!$value && !$get('product_image')) {
            //             $fail('Either an image URL or an image upload is required.');
            //         }
            //         if ($value && $get('product_image')) {
            //             $fail('Only one of Image Upload or Image URL allowed.');
            //         }
            //     })
            //     ->afterStateUpdated(fn ($state, $set) =>
            //         $state ? $set('product_image', null) : null
            //     ),



            /*
            |--------------------------------------------------------------------------
            | IMAGE FILE
            |--------------------------------------------------------------------------
            */

            FileUpload::make('product_image')
                    ->label('Product Image')
                    ->disk('public')
                    ->directory('product/' . date('FY'))
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
                        blank($get('product_image'))
                        && blank($get('product_image_url'))
                    )
                    ->validationMessages([
                        'required' =>
                        'Either Image Upload or Image URL is required.',
                    ]),

            // FileUpload::make('product_image')
            //     ->disk('public')
            //     ->directory('product/' . date('FY'))
            //     ->image()
            //     ->maxSize(310)
            //     ->acceptedFileTypes(['image/jpeg', 'image/png'])
            //     ->enableDownload()
            //     ->requiredWithout('product_image_url')
            //     ->rule(fn ($get) => function ($attribute, $value, $fail) use ($get) {
            //         if ($value && $get('product_image_url')) {
            //             $fail('Only one of Image Upload or Image URL allowed.');
            //         }
            //     })
            //     ->afterStateUpdated(fn ($state, $set) =>
            //         $state ? $set('product_image_url', null) : null
            //     ),


            RichEditor::make('short_description')
                ->label('Short Description')
                ->columnSpanFull()
                // ✅ THIS is the correct max character limit in Filament 4
                ->maxLength(300)
                ->helperText('Maximum 300 characters allowed')
                 ->dehydrateStateUsing(fn ($state) =>
                    blank(trim(strip_tags($state))) ? null : $state
                ),
 
            RichEditor::make('long_description')
                ->label('Long Description')
                ->columnSpanFull()
                // ✅ THIS is the correct max character limit in Filament 4
                ->maxLength(1200)
                ->helperText('Maximum 1200 characters allowed')
                 ->dehydrateStateUsing(fn ($state) =>
                    blank(trim(strip_tags($state))) ? null : $state
                ),
 
            /*
            |--------------------------------------------------------------------------
            | FLAGS
            |--------------------------------------------------------------------------
            */

            Toggle::make('status')
                ->default(true)
                ->dehydrateStateUsing(fn ($state) => $state ? 1 : 0),

            Toggle::make('is_new')
                ->default(false)
                ->dehydrateStateUsing(fn ($state) => $state ? 1 : 0),

            Toggle::make('is_trending')
                ->default(false)
                ->dehydrateStateUsing(fn ($state) => $state ? 1 : 0),

            Toggle::make('latest_product_show_in_popup')
                ->default(false)
                ->dehydrateStateUsing(fn ($state) => $state ? 1 : 0),

            Toggle::make('is_notify')
                ->default(false)
                ->dehydrateStateUsing(fn ($state) => $state ? 1 : 0),
 
            /*
            |--------------------------------------------------------------------------
            | ORDER BY
            |--------------------------------------------------------------------------
            */

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
