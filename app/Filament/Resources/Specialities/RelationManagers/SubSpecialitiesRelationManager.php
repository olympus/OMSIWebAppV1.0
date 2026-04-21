<?php

namespace App\Filament\Resources\Specialities\RelationManagers;

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
use App\Models\Speciality; 
use Illuminate\Validation\Rule;

class SubSpecialitiesRelationManager extends RelationManager
{
    protected static string $relationship = 'subSpecialities';

    protected static ?string $recordTitleAttribute = 'specialities_name';
    
    public function table(Table $table): Table
    {
        return $table
            ->columns([
                //ImageColumn::make('specialities_image')->label('Image')->square(),
                TextColumn::make('specialities_name')->label('Name')->searchable(),
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
                DeleteAction::make()
                    ->requiresConfirmation()
                    ->modalHeading('Delete Speciality')
                    ->modalDescription('If you delete this speciality, all related sub categories and linked product relations will also be deleted. Do you want to continue?')
                    ->modalSubmitActionLabel('Yes, Delete Everything')
                    ->successNotificationTitle('Speciality and related data deleted successfully'),
                //DeleteAction::make(),
            ]);
    }
 
    public function form(Schema $schema): Schema
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
                    ->rule('max:75') // strict validation
                    ->required()
                    ->regex('/^[A-Za-z0-9]+( [A-Za-z0-9]+)*$/')
                    ->rule(fn ($record) =>
                        Rule::unique('specialities', 'specialities_name')
                            ->where(function ($query) {
                                $query
                                    ->whereNull('deleted_at')
                                    ->where('parent_id', $this->ownerRecord->id);
                            })
                            ->ignore($record?->id)
                    )
                    ->validationMessages([
                        'required' => 'Sub Speciality name is required.',
                        'regex' => 'No leading/trailing spaces and only single space between words allowed.',
                        'unique' => 'This sub speciality name already exists.',
                    ]),

                TextInput::make('slug')
                    ->label('Slug')
                    ->required()
                    ->rule('max:75') // strict validation
                    ->rule('regex:/^[a-z0-9]+(-[a-z0-9]+)*$/')
                    ->rule(fn ($record) =>
                        Rule::unique('specialities', 'slug')
                            ->where(function ($query) {
                                $query
                                    ->whereNull('deleted_at')
                                    ->where('parent_id', $this->ownerRecord->id);
                            })
                            // ->whereNull('deleted_at')
                            ->ignore($record?->id)
                    )
                    ->validationMessages([
                        'regex' => 'Slug format is invalid.',
                        'unique' => 'This slug already exists.',
                    ]), 

                /*
                |--------------------------------------------------------------------------
                | Image Upload
                |--------------------------------------------------------------------------
                */

                FileUpload::make('specialities_image')
                    ->label('Speciality Image')
                    ->disk('public')
                    ->directory('sub_speciality/' . date('FY'))
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
                        'required' =>
                        'Either Image Upload or Image URL is required.',
                        'url' =>
                        'Enter valid URL',
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
