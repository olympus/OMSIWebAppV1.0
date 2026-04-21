<?php

namespace App\Filament\Resources\Specialities\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Actions\CreateAction;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use App\Models\Category;
use Filament\Forms\Get;

class SpecialityCategoriesRelationManager extends RelationManager
{
    protected static string $relationship = 'specialityCategories';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('sub_speciality_id')
                    ->label('Sub Speciality')
                    ->options(function () {
                        $owner = $this->getOwnerRecord();

                        return \App\Models\Speciality::where('parent_id', $owner->id)
                            ->pluck('specialities_name', 'id');
                    })
                    ->required()
                    ->searchable()
                    ->preload(),

                Select::make('category_id')
                    ->label('Category')
                    ->options(Category::whereNull('parent_id')->whereNull('child_id')->pluck('categories_name', 'id'))
                    ->required()
                    ->reactive()
                    ->afterStateUpdated(fn (callable $set) => $set('sub_category_id', null)),

                Select::make('sub_category_id')
                    ->label('Sub Category')
                    ->options(function (callable $get) {
                        $categoryId = $get('category_id');
                        if (!$categoryId) {
                            return [];
                        }
                        return Category::where('parent_id', $categoryId)->whereNull('child_id')->pluck('categories_name', 'id');
                    }),
                
                TextInput::make('order')
                    ->numeric(),

                Toggle::make('status')
                    ->required()
                    ->default(true),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([

                TextColumn::make('speciality.specialities_name')
                    ->label('Speciality')
                    ->searchable(),

                TextColumn::make('subSpeciality.specialities_name')
                    ->label('Sub Speciality')
                    ->searchable(),

                TextColumn::make('category.categories_name')
                    ->label('Category')
                    ->searchable(),

                TextColumn::make('subcategory.categories_name')
                    ->label('Sub Category')
                    ->searchable(),

                TextColumn::make('order')
                    ->sortable(),

                IconColumn::make('status')
                    ->boolean(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make(),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }
}
