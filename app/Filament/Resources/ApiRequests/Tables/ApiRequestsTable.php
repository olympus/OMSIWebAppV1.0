<?php

namespace App\Filament\Resources\ApiRequests\Tables;

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

class ApiRequestsTable
{
    public $tableFilterFromDate = null;
    public $tableFilterToDate = null;

    public static function configure(Table $table): Table
    {   
        $month = date('m');
        if ($month >= 4) {
            $y = date('Y');
            $pt = date('Y', strtotime('+1 year'));
        } else {
            $pt = date('Y');
            $y = date('Y', strtotime('-1 year'));
        }

        $from_date = $y . "-04-01";
        $to_date = $pt . "-03-31";

        return $table
            // ->filters([  
            //     Filter::make('created_at')
            //         ->label('Filter')
            //         ->form([
            //             DatePicker::make('from')->label('From Date'),
            //             DatePicker::make('to')->label('To Date'),
            //         ])->columns(2)
            //         ->query(function (Builder $query, array $data): Builder {
            //             $from = $data['from'] ?? null;
            //             $to = $data['to'] ?? null;

            //             if ($from || $to) {
            //                 $from = $from ? $from . ' 00:00:00' : null;
            //                 $to = $to ? $to . ' 23:59:59' : null;

            //                 if ($from && $to) {
            //                     return $query->whereBetween('created_at', [$from, $to]);
            //                 } elseif ($from) {
            //                     return $query->where('created_at', '>=', $from);
            //                 } elseif ($to) {
            //                     return $query->where('created_at', '<=', $to);
            //                 }
            //             }

            //             // Default financial year
            //             $month = date('m');
            //             if ($month >= 4) {
            //                 $y = date('Y');
            //                 $pt = date('Y', strtotime('+1 year'));
            //             } else {
            //                 $pt = date('Y');
            //                 $y = date('Y', strtotime('-1 year'));
            //             }

            //             return $query->whereBetween('created_at', [$y . "-04-01 00:00:00", $pt . "-03-31 23:59:59"])->orderBy('created_at','desc');
            //         }),
            // ], layout: FiltersLayout::AboveContent)
            ->columns([
                TextColumn::make('id')->sortable(),
                TextColumn::make('identifier')->wrap()->searchable(),
                TextColumn::make('request_type')->wrap()->searchable(),
                TextColumn::make('request_url')->wrap()->searchable(),
                TextColumn::make('request_body')->wrap()->searchable(),
                TextColumn::make('created_at')->dateTime()->sortable(),
                TextColumn::make('updated_at')->dateTime()->sortable(),
            ])
            ->filters([
                Filter::make('created_at')
                    ->form([
                        DatePicker::make('created_from'),
                        DatePicker::make('created_until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        $month = date('m');
                        if ($month >= 4) {
                            $y = date('Y');
                            $pt = date('Y', strtotime('+1 year'));
                        } else {
                            $pt = date('Y');
                            $y = date('Y', strtotime('-1 year'));
                        }
                        $from_date = $y . "-04-01";
                        $to_date = $pt . "-03-31";

                        return $query
                            ->when($data['created_from'] && $data['created_until'], function ($q) use ($data) {
                                $q->whereDate('created_at', '>=', $data['created_from'])
                                  ->whereDate('created_at', '<=', $data['created_until']);
                            })
                            ->when(!$data['created_from'] && !$data['created_until'], function ($q) use ($from_date, $to_date) {
                                $q->whereDate('created_at', '>=', $from_date)
                                  ->whereDate('created_at', '<=', $to_date);
                            });
                    }),
            ])
            
            ->recordActions([
                ViewAction::make(),
                // EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    //DeleteBulkAction::make(),
                ]),
            ]);
    }

    
}
