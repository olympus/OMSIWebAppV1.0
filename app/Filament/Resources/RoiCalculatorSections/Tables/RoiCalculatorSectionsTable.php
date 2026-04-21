<?php

namespace App\Filament\Resources\RoiCalculatorSections\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class RoiCalculatorSectionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->searchable(),
                // TextColumn::make('video_type')
                //     ->searchable(),
                // TextColumn::make('video_file')
                //     ->searchable(),
                // TextColumn::make('thumbnail')
                //     ->searchable(),
                IconColumn::make('status')
                    ->boolean(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(), 
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                // BulkActionGroup::make([
                //     DeleteBulkAction::make(),
                // ]),
            ]);
    }
}
