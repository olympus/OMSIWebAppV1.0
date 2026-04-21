<?php

namespace App\Filament\Resources\Specialities\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
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
use Filament\Forms\Components\Select;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Utilities\Get;
use App\Models\Speciality;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Str;

class SubSubSpecialitiesRelationManager extends RelationManager
{
    // Relation on Speciality model
    protected static string $relationship = 'subSubSpecialities';

    protected static ?string $recordTitleAttribute = 'specialities_name';

    protected function getTableQuery(): Builder | Relation
    {
        // Prefer getting the relationship query directly to avoid parent returning null
        $relationship = $this->getRelationship();

        if ($relationship) {
            $query = $relationship->getQuery();

            return $query->with('child');
        }

        // Fallback to parent (if it returns a query)
        $parentQuery = parent::getTableQuery();

        if ($parentQuery) {
            return $parentQuery->with('child');
        }

        // Final fallback: query categories that are subSub (parent_id = owner id and child_id not null)
        return Speciality::query()
            ->where('parent_id', $this->getOwnerRecord()->id)
            ->whereNotNull('child_id')
            ->with('child');
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('specialities_image')->label('Image')->square(),
                TextColumn::make('specialities_name')->label('Name')->searchable()->sortable(),
                TextColumn::make('orderby')->label('Order')->sortable(),
                ToggleColumn::make('status')->label('Active'),
                TextColumn::make('child.specialities_name')->label('Linked Sub Speciality')->sortable()->searchable()->default('-'),
            ])
            ->headerActions([
                CreateAction::make()->modalWidth('lg')->label('Create'),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('specialities_name')
                    ->live(onBlur: true)
                    ->afterStateUpdated(function ($state, callable $set) {
                        $set('slug', Str::slug($state));
                    })
                    ->required()->label('Name'),

                TextInput::make('slug')
                    ->required()
                    ->label('Slug'),

                FileUpload::make('specialities_image')->image()->label('Image')->nullable(),
                Toggle::make('status')->default(true),
                TextInput::make('orderby')->numeric()->nullable(),

                // parent_id is fixed to the owner category id
                Hidden::make('parent_id')
                    ->default(fn ($record) => $this->getOwnerRecord()->id)
                    ->required()
                    ->disabled(),

                // child_id selects which Sub Speciality this Speciality links to
                Select::make('child_id')
                    ->label('Sub Speciality')
                    ->options(function (Get $get) {
                        $selectedId = $get('child_id');

                        // Use Speciality to fetch candidate child Specialities (those that have a parent_id)
                        $items = Speciality::where('parent_id',$this->getOwnerRecord()->id)
                            ->whereNull('child_id')
                            ->orderBy('specialities_name')
                            ->get()
                            ->mapWithKeys(fn ($c) => [$c->id => $c->specialities_name])
                            ->toArray();

                        // Ensure the currently selected child Speciality is present when editing
                        if ($selectedId && ! array_key_exists($selectedId, $items)) {
                            $c = Speciality::where('id', $selectedId)->first();

                            if ($c) {
                                $items[$c->id] = $c->specialities_name;
                            }
                        }

                        return $items;
                    })
                    ->searchable()
                    ->required(),
            ]);
    }
}
