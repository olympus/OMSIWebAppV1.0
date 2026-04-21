<?php

namespace App\Filament\Resources\ActivityLogs\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Enums\FiltersLayout;

class ActivityLogsTable
{
    public static function configure(Table $table): Table
    {
        return $table

        ->filters([
            Filter::make('created_at')
                ->label('Filter')
                ->form([
                    DatePicker::make('from')->label('From Date'),
                    DatePicker::make('to')->label('To Date'),
                ])
                ->columns(2)
                ->query(function (Builder $query, array $data): Builder {

                    $from = $data['from'] ?? null;
                    $to = $data['to'] ?? null;

                    if ($from) {
                        $query->whereDate('created_at', '>=', $from);
                    }

                    if ($to) {
                        $query->whereDate('created_at', '<=', $to);
                    }

                    return $query->latest();
                }),

        ], layout: FiltersLayout::AboveContent)


        ->columns([

            TextColumn::make('id')
                ->label('ID')
                ->sortable(),

            TextColumn::make('log_name')
                ->label('Type')
                ->badge()
                ->color('primary')
                ->sortable(),

            BadgeColumn::make('event')
                ->colors([
                    'success' => 'Created',
                    'warning' => 'Updated',
                    'danger' => 'Deleted',
                ])
                ->label('Event'),

            TextColumn::make('description')
                ->label('Description')
                ->searchable()
                ->wrap(),

            TextColumn::make('causer.name')
                ->label('User')
                ->sortable()
                ->searchable(),

            TextColumn::make('subject_type')
                ->label('Module')
                ->formatStateUsing(fn ($state) => class_basename($state))
                ->sortable(),

            TextColumn::make('subject_id')
                ->label('Record ID')
                ->sortable(),

            TextColumn::make('properties')
                ->label('Data')
                ->formatStateUsing(fn ($state) =>
                    json_encode(json_decode($state), JSON_PRETTY_PRINT)
                )
                ->toggleable(isToggledHiddenByDefault: true),

            TextColumn::make('batch_uuid')
                ->label('Batch')
                ->toggleable(isToggledHiddenByDefault: true),

            TextColumn::make('created_at')
                ->label('Date')
                ->dateTime('d M Y, h:i A')
                ->sortable(),

        ])


        ->defaultSort('created_at', 'desc')


        ->toolbarActions([
            BulkActionGroup::make([
                DeleteBulkAction::make(),
            ]),
        ]);

    }
}