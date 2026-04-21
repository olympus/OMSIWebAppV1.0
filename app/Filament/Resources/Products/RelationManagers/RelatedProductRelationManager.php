<?php

namespace App\Filament\Resources\Products\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Actions\CreateAction;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Schemas\Schema;
use App\Models\Product;

class RelatedProductRelationManager extends RelationManager
{
    protected static string $relationship = 'productCompatible';

    protected static ?string $recordTitleAttribute = 'compatible_product_id';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('compatibleProduct.product_name')
                    ->label('Related Product')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('orderby')
                    ->label('Order By')
                    ->sortable(),

                ToggleColumn::make('status')
                    ->label('Status'),

                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                // add filters if needed
            ])
            ->defaultSort('id','desc')
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
                DeleteAction::make(),
            ]);
    }


    public function form(Schema $schema): Schema
    {
        return $schema->components([

            // Select::make('compatible_product_id')
            //     ->label('Select Related Product')
            //     ->options(
            //         Product::where('status', 1)
            //             ->pluck('product_name', 'id')
            //     )
            //     ->searchable()
            //     ->required()
            //     ->validationMessages([
            //         'required' => 'Please select a related product.',
            //     ]),

            Select::make('compatible_product_id')
                ->label('Select Compatible Product')
                ->options(function ($livewire) {
                    return Product::where('status', 1)
                        ->where('id', '!=', $livewire->ownerRecord->id)
                        ->pluck('product_name', 'id');
                })
                ->searchable()
                ->required()
                ->unique(
                    table: 'related_products',
                    column: 'compatible_product_id',
                    modifyRuleUsing: function ($rule, $get, $livewire) {
                        return $rule->where('product_id', $livewire->ownerRecord->id);
                    },
                    ignoreRecord: true
                )
                ->validationMessages([
                    'required' => 'Please select a related product.',
                    'unique' => 'This product is already added as compatible.',
                ]),

            TextInput::make('orderby')
                ->label('Order')
                ->numeric()
                ->minValue(1)
                ->default(null)
                ->dehydrated(fn ($state) => filled($state))
                ->validationMessages([
                    'min_value' => 'Order must be greater than 0.',
                ]),   
                
            Toggle::make('status')
                ->default(true),
        ]);
    }
}
