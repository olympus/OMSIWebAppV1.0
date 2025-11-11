<?php

namespace App\Filament\Resources\ActivityLogs\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
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
                    ])->columns(2)
                    ->query(function (Builder $query, array $data): Builder {
                        $from = $data['from'] ?? null;
                        $to = $data['to'] ?? null;

                        if ($from || $to) {
                            $from = $from ? $from . ' 00:00:00' : null;
                            $to = $to ? $to . ' 23:59:59' : null;

                            if ($from && $to) {
                                return $query->whereBetween('created_at', [$from, $to]);
                            } elseif ($from) {
                                return $query->where('created_at', '>=', $from);
                            } elseif ($to) {
                                return $query->where('created_at', '<=', $to);
                            }
                        }

                        // Default financial year
                        $month = date('m');
                        if ($month >= 4) {
                            $y = date('Y');
                            $pt = date('Y', strtotime('+1 year'));
                        } else {
                            $pt = date('Y');
                            $y = date('Y', strtotime('-1 year'));
                        }

                        return $query->whereBetween('created_at', [$y . "-04-01 00:00:00", $pt . "-03-31 23:59:59"])->orderBy('created_at','desc');
                    }),
            ], layout: FiltersLayout::AboveContent)
            ->columns([
                TextColumn::make('id')
                    ->searchable(),
                TextColumn::make('causer_id')
                    ->sortable()
                    ->label('User ID'),
                TextColumn::make('log_name')
                    ->searchable()
                    ->label('Activity Type'),
                TextColumn::make('subject_type')
                    ->searchable()
                    ->label('Subject Type'),
                TextColumn::make('subject_id')
                    ->sortable()
                    ->label('On Data ID'),
                
                TextColumn::make('causer_type')
                    ->searchable()
                    ->label('Caused By'),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->label('Timestamp'),
                
            ])
            ->recordActions([
                // ViewAction::make(),
                // EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
