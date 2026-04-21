<?php

namespace App\Filament\Resources\SubSpecialities\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use App\Models\User;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class SubSpecialitiesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('speciality.specialities_name')
                    ->label('Speciality')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('sub_specialities_name')
                    ->searchable(),
                ImageColumn::make('sub_specialities_image'),
                IconColumn::make('status')
                    ->boolean(),
                TextColumn::make('orderby')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('created_by')
                    ->label('Created By')
                    ->formatStateUsing(fn ($state) => optional(User::find($state))->name ?? '-')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}
