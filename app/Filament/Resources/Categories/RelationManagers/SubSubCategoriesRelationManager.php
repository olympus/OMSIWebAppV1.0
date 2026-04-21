<?php

namespace App\Filament\Resources\Categories\RelationManagers;

use App\Models\SubCategory;
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
use App\Models\Category;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Str;

class SubSubCategoriesRelationManager extends RelationManager
{
    // Relation on Category model
    protected static string $relationship = 'subSubCategories';

    protected static ?string $recordTitleAttribute = 'categories_name';

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
        return Category::query()
            ->where('parent_id', $this->getOwnerRecord()->id)
            ->whereNotNull('child_id')
            ->with('child');
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('categories_image')->label('Image')->square(),
                TextColumn::make('categories_name')->label('Name')->searchable()->sortable(),
                TextColumn::make('orderby')->label('Order')->sortable(),
                ToggleColumn::make('status')->label('Active'),
                TextColumn::make('child.categories_name')->label('Linked Sub Category')->sortable()->searchable()->default('-'),
            ])
            ->headerActions([
                CreateAction::make()->modalWidth('lg')->label('Create')
                ->modalHeading('Create')
                ->modalSubmitActionLabel('Create'),
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
                TextInput::make('categories_name')
                    ->live(onBlur: true)
                    ->afterStateUpdated(function ($state, callable $set) {
                        $set('slug', Str::slug($state));
                    })
                    ->required()->label('Name'),

                TextInput::make('slug')
                    ->required()
                    ->label('Slug'),

                FileUpload::make('categories_image')->image()->label('Image')->nullable(),
                Toggle::make('status')->default(true),
                TextInput::make('orderby')->numeric()->nullable(),

                // parent_id is fixed to the owner category id
                Hidden::make('parent_id')
                    ->default(fn ($record) => $this->getOwnerRecord()->id)
                    ->required()
                    ->disabled(),

                // child_id selects which SubCategory this Category links to
                Select::make('child_id')
                    ->label('Subcategory')
                    ->options(function (Get $get) {
                        $selectedId = $get('child_id');
                        
                        // Use Category to fetch candidate child Categories (those that have a parent_id)
                        $items = Category::where('parent_id',$this->getOwnerRecord()->id)
                            ->whereNull('child_id')
                            ->orderBy('categories_name')
                            ->get()
                            ->mapWithKeys(fn ($c) => [$c->id => $c->categories_name])
                            ->toArray();

                        // Ensure the currently selected child Category is present when editing
                        if ($selectedId && ! array_key_exists($selectedId, $items)) {
                            $c = Category::where('id', $selectedId)->first();

                            if ($c) {
                                $items[$c->id] = $c->categories_name;
                            }
                        }

                        return $items;
                    })
                    ->searchable()
                    ->required(),
            ]);
    }
}
