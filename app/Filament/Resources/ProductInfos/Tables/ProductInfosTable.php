<?php

namespace App\Filament\Resources\ProductInfos\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ProductInfosTable
{
    public static function configure(Table $table): Table
    {
        return $table
        ->modifyQueryUsing(function (Builder $query) {
                return $query->orderBy('created_at','desc');
            })
            ->columns([
                TextColumn::make('id')
                    ->searchable()
                    ->sortable()
                    ->label('Product Id'),
                TextColumn::make('service_requests_id')
                    ->sortable()
                    ->searchable()
                    ->label('Service Request ID'),
                TextColumn::make('pd_name')
                    ->searchable()
                    ->sortable()
                    ->label('pd_name'),
                TextColumn::make('pd_serial')
                    ->searchable()
                    ->sortable()
                    ->label('Serial'),
                TextColumn::make('pd_description')
                    ->searchable()
                    ->sortable()
                    ->label('Description'),
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
                // EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
