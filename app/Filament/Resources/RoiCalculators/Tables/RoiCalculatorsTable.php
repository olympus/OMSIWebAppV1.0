<?php

namespace App\Filament\Resources\RoiCalculators\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class RoiCalculatorsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('customer.first_name') 
                    ->searchable(),
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('email')
                    ->label('Email address')
                    ->searchable(),
                TextColumn::make('mobile')
                    ->searchable(),
                TextColumn::make('hospital_name')
                    ->searchable(),
                TextColumn::make('speciality')
                    ->searchable(),
                TextColumn::make('state')
                    ->searchable(),
                TextColumn::make('city')
                    ->searchable(),
                TextColumn::make('pincode')
                    ->searchable(),
                // TextColumn::make('customer_status')
                //     ->searchable(),
                // TextColumn::make('processor_profile')
                //     ->searchable(),
                // TextColumn::make('endoscopy_suite')
                //     ->searchable(),
                // TextColumn::make('procedure_performer')
                //     ->searchable(),
                // TextColumn::make('procedures_performed')
                //     ->searchable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->defaultSort('id','desc')
            ->recordActions([
                ViewAction::make(),
                //EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    //DeleteBulkAction::make(),
                ]),
            ]);
    }
}
