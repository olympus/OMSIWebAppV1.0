<?php

namespace App\Filament\Resources\Badges\RelationManagers;

use App\Models\Product;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Table;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class BadgeProductsRelationManager extends RelationManager
{
    protected static string $relationship = 'badgeProducts';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('product.product_name')->label('Product')->sortable()->searchable(),
                IconColumn::make('status')->boolean()->label('Active'),
            ])
            ->filters([
                // add filters if needed
            ])
            ->headerActions([
                CreateAction::make()->modalWidth('lg'),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('product_id')
                ->label('Product')
                ->options(function () {
                    return Product::orderBy('product_name')
                        ->get()
                        ->mapWithKeys(fn ($p) => [$p->id => $p->product_name])
                        ->toArray();
                })
                ->searchable()
                ->preload()
                ->required(),

            Toggle::make('status')
                ->label('Active')
                ->default(true),
        ]);
    }
}
