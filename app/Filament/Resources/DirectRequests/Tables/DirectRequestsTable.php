<?php

namespace App\Filament\Resources\DirectRequests\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class DirectRequestsTable
{
    public static function configure(Table $table): Table
    {
        return $table
        ->modifyQueryUsing(function (Builder $query) {
                return $query->orderBy('created_at','desc');
            })
            ->columns([
                 TextColumn::make('id') 
                    ->sortable(),
                TextColumn::make('sap_id')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('status')
                    ->sortable(),
                TextColumn::make('fse_code')
                    ->sortable()
                    ->searchable()
                    ->label('FSE Code'),
                TextColumn::make('customer_name')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('customer_code')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('customer_city')
                    ->sortable()
                    ->searchable(),
                 TextColumn::make('customer_state')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('prod_model_no')
                    ->sortable()
                    ->searchable()
                    ->label('Prod Model No.'),
                TextColumn::make('prod_material')
                    ->sortable()
                    ->searchable()
                    ->label('Prod Material'),
                TextColumn::make('prod_serial_no')
                    ->sortable()
                    ->searchable()
                    ->label('Prod Serial No'),
                TextColumn::make('prod_equipment_no')
                    ->sortable()
                    ->searchable()
                    ->label('Prod Equipment No'),
                TextColumn::make('prod_material_description')
                    ->sortable()
                    ->searchable()
                    ->label('Prod Material Desc.'),
                TextColumn::make('sort_field')
                    ->sortable()
                    ->searchable()
                    ->label('Sort Field'),
                TextColumn::make('contract_desc')
                    ->sortable()
                    ->searchable()
                    ->label('Contract Description'),
                TextColumn::make('branch')
                    ->sortable()
                    ->searchable()
                    ->label('Branch'),
                TextColumn::make('zone')
                    ->sortable()
                    ->searchable()
                    ->label('Zone'),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
                    // ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable(),
                    // ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make(),
                //EditAction::make(),
            ])
            ->toolbarActions([
                // BulkActionGroup::make([
                //     DeleteBulkAction::make(),
                // ]),
            ]);
    }
}
