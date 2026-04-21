<?php

namespace App\Filament\Resources\Categories\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Hidden;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Str;
use App\Models\Category; 
use Illuminate\Validation\Rule;

class SubCategoriesRelationManager extends RelationManager
{
    protected static string $relationship = 'subCategories';

    protected static ?string $recordTitleAttribute = 'categories_name';

    // protected function getTableQuery(): Builder | Relation
    // {
    //     $query = parent::getTableQuery();

    //     // Only show direct child categories (not sub-subcategories which have child_id set)
    //     return $query->whereNull('child_id');
    // }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                //ImageColumn::make('categories_image')->label('Image')->square(),
                TextColumn::make('categories_name')->label('Name')->searchable(),
                TextColumn::make('slug')->label('Slug')->searchable(),
                TextColumn::make('orderby')->label('Order')->sortable(),
                ToggleColumn::make('status')->label('Active'),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->headerActions([
                CreateAction::make()
                    ->slideOver()
                    //->modalWidth('lg')
                    ->label('Create')
                    ->modalHeading('Create')
                    ->modalSubmitActionLabel('Create'),
            ])
            ->recordActions([
                EditAction::make(),
                //DeleteAction::make(),
                DeleteAction::make()
                    ->requiresConfirmation()
                    ->modalHeading('Delete Category')
                    ->modalDescription('If you delete this category, all related sub categories and linked product relations will also be deleted. Do you want to continue?')
                    ->modalSubmitActionLabel('Yes, Delete Everything')
                    ->successNotificationTitle('Category and related data deleted successfully'),
            ]);
    } 

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([

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
                            ->where(function ($query) {
                                $query
                                    ->whereNull('deleted_at')
                                    ->where('parent_id', $this->ownerRecord->id);
                            })
                            ->ignore($record?->id)
                    )
                    ->validationMessages([
                        'required' => 'Sub Category name is required.',
                        'regex' => 'No leading/trailing spaces and only single space between words allowed.',
                        'unique' => 'This sub category name already exists.',
                    ]),

                TextInput::make('slug')
                    ->label('Slug')
                    ->required()
                    ->rule('regex:/^[a-z0-9]+(-[a-z0-9]+)*$/')
                    ->rule(fn ($record) =>
                        Rule::unique('categories', 'slug')
                            ->where(function ($query) {
                                $query
                                    ->whereNull('deleted_at')
                                    ->where('parent_id', $this->ownerRecord->id);
                            })
                            // ->whereNull('deleted_at')
                            ->ignore($record?->id)
                    )
                    ->rule('max:75') // strict validation
                    
                    ->validationMessages([
                        'regex' => 'Slug format is invalid.',
                        'unique' => 'This slug already exists.',
                    ]), 

                /*
                |--------------------------------------------------------------------------
                | Image Upload
                |--------------------------------------------------------------------------
                */

                FileUpload::make('categories_image')
                    ->label('Categories Image')
                    ->disk('public')
                    ->directory('sub_category/' . date('FY'))
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
