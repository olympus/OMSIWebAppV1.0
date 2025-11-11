<?php

namespace App\Filament\Resources\EmployeeTeams\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Table;
use Filament\Actions\Action;

class EmployeeTeamsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->sortable(),
                TextColumn::make('name')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('email')
                    ->label('Email address')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('designation')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('employee_code')
                    ->sortable()
                    ->searchable(),
                IconColumn::make('disabled')
                ->label('Is Disabled')
                ->boolean()
                ->trueIcon('heroicon-o-x-circle')   // Show X when disabled = 1
                ->falseIcon('heroicon-o-check-circle') // Show check when disabled = 0
                ->trueColor('danger')
                ->falseColor('success'),
                ImageColumn::make('image')
                    ->label('Image')
                    ->getStateUsing(fn ($record) => $record->image ? asset('storage/' . $record->image) : null)
                    ->sortable()
                    ->searchable(false),
                TextColumn::make('mobile')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('gender')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('category')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('branch')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('zone')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('escalation_1')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('escalation_2')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('escalation_3')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('escalation_4')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
                    // ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    // ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                // ViewAction::make(),
                EditAction::make(),
                Action::make('toggleDisabled')
                ->label(fn ($record) => $record->disabled == 0 ? 'Disable' : 'Enable')
                ->color(fn ($record) => $record->disabled == 0 ? 'danger' : 'success')
                ->icon(fn ($record) => $record->disabled == 0 ? 'heroicon-o-x-circle' : 'heroicon-o-check-circle')
                ->action(function ($record) {
                    $record->disabled = $record->disabled == 0 ? 1 : 0;
                    $record->save();
                }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
