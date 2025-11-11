<?php

namespace App\Filament\Resources\ServiceMasters\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ServiceMastersTable
{
    public static function configure(Table $table): Table
    {
        return $table
        ->modifyQueryUsing(function (Builder $query) {
                return $query->where('request_type','service')->orderBy('created_at','desc');
            })
            ->columns([
                 TextColumn::make('id') 
                    ->sortable(),
                TextColumn::make('states')
                    ->searchable()
                    ->sortable()
                    ->label('States'),
                TextColumn::make('departments')
                    ->searchable()
                    ->sortable()
                    ->label('Departments'),
                TextColumn::make('to_emails')
                    ->searchable()
                    ->sortable()
                    ->label('To Emails'),
                TextColumn::make('cc_emails')
                    ->searchable()
                    ->sortable()
                    ->label('CC Emails'),
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
                // ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
