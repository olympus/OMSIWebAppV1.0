<?php

namespace App\Filament\Resources\Products\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Actions\CreateAction;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Illuminate\Validation\Rule;
use App\Models\Category;

class ProductCategoryRelationManager extends RelationManager
{
    protected static string $relationship = 'productCategories';

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function ($query) {
            $query
                ->whereHas('category')
                ->whereHas('subCategory');
        })
            ->columns([
                TextColumn::make('category.categories_name')
                    ->label('Category')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('subCategory.categories_name')
                    ->label('Sub Category')
                    // ->default('-')
                    ->searchable()
                    ->sortable(),

                // TextColumn::make('subCategory.categories_name')
                //     ->label('Sub Category')
                //     ->searchable()
                //     ->sortable(),

                ToggleColumn::make('status'),

                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                // add filters if needed
            ])
            ->defaultSort('id','desc')
            ->headerActions([
                CreateAction::make()->slideOver(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components([

            Select::make('category_id')
                ->label('Select Category')
                ->options(
                    Category::whereNull('parent_id')
                        ->where('status', 1)
                        ->pluck('categories_name', 'id')
                )
                ->searchable()
                ->required()
                ->reactive()
                ->afterStateUpdated(fn (callable $set) => $set('subcategory_id', null)),

            Select::make('subcategory_id')
                ->label('Select Sub Category')
                ->options(function ($get) {
                    return Category::where('parent_id', $get('category_id'))
                        ->where('status', 1)
                        ->pluck('categories_name', 'id');
                })
                ->searchable()
                ->required()
                ->rules([
                    fn ($get, $record): \Closure => function (string $attribute, $value, \Closure $fail) use ($get, $record) {
                        $exists = \DB::table('product_categories')
                            ->where('product_id', $this->getOwnerRecord()->id)
                            ->where('category_id', $get('category_id'))
                            ->where('subcategory_id', $value)
                            ->whereNULL('deleted_at')
                            ->when($record, fn($q) => $q->where('id', '!=', $record->id))
                            ->exists();

                        if ($exists) {
                            $fail('This category and sub category combination is already assigned to this product.');
                        }
                    },
                ]),

            Toggle::make('status')
                ->default(true),
        ]);
    }

    protected function getFormValidationRules(): array
    {
        return [
            'category_id' => [
                'required',
                Rule::unique('product_categories')
                    ->where(function ($query) {
                        return $query
                            ->where('product_id', $this->ownerRecord->id)
                            ->where('subcategory_id', request()->input('subcategory_id'));
                    })
                    ->ignore($this->record),
            ],
        ];
    }

    protected function getFormValidationMessages(): array
    {
        return [
            'category_id.unique' => 'This category and sub category combination already exists for this product.',
        ];
    }
}
