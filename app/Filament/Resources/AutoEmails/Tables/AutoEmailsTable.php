<?php

namespace App\Filament\Resources\AutoEmails\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class AutoEmailsTable
{
    public static function configure(Table $table): Table
    {
        return $table->modifyQueryUsing(function (Builder $query) {
                return $query->orderBy('created_at','desc');
            })
            ->columns([
                TextColumn::make('request_type')
                    ->searchable(),
                TextColumn::make('states')
                    ->searchable(),
                TextColumn::make('departments')
                    ->searchable(),
                TextColumn::make('to_emails')
                    ->searchable(),
                TextColumn::make('cc_emails')
                    ->searchable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
