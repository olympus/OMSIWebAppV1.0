<?php

namespace App\Filament\Resources\EnquiryMasters\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class EnquiryMastersTable
{
    public static function configure(Table $table): Table
    {
        return $table
        ->modifyQueryUsing(function (Builder $query) {
                return $query->where('request_type','enquiry');
            })
            ->columns([
                 TextColumn::make('id') 
                    ->sortable(),
                TextColumn::make('states')
                    ->searchable()
                    ->sortable()
                    ->wrap()
                    ->label('States'),
                TextColumn::make('departments')
                    ->searchable()
                    ->sortable()
                    ->wrap()
                    ->label('Departments'),
                TextColumn::make('to_emails')
                    ->searchable()
                    ->sortable()
                    ->wrap()
                    ->label('To Emails'),
                TextColumn::make('cc_emails')
                    ->searchable()
                    ->sortable()
                    ->wrap()
                    ->label('CC Emails'),

                TextColumn::make('created_at') 
                    ->dateTime()
                    ->sortable(query: function (Builder $query, string $direction) {
                        $query->orderBy('created_at', $direction);
                    }),
                
                TextColumn::make('updated_at') 
                    ->dateTime()
                    ->sortable(query: function (Builder $query, string $direction) {
                        $query->orderBy('updated_at', $direction);
                    }),
  
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
                    //DeleteBulkAction::make(),
                ]),
            ]);
    }
}
