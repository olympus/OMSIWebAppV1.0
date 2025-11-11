<?php

namespace App\Filament\Resources\OlympusCustomers\Tables;

use App\Models\Hospitals;
use App\Models\Customers;
use App\Models\Departments;;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Forms\Components\DatePicker;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Facades\Excel;
class OlympusCustomersTable
{
    public static function configure(Table $table): Table
    {
        //  $month = date('m');
        // if ($month >= 4) {
        //     $y = date('Y');
        //     $pt = date('Y', strtotime('+1 year'));
        // } else {
        //     $pt = date('Y');
        //     $y = date('Y', strtotime('-3 year'));
        // }

        // $from_date = $y . "-04-01 00:00:00";
        // $to_date   = $pt . "-03-31 23:59:59";
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
            // ->modifyQueryUsing(function (Builder $query) {
            //     $month = date('m');
            //     if ($month >= 4) {
            //         $y = date('Y');
            //         $pt = date('Y', strtotime('+1 year'));
            //     } else {
            //         $pt = date('Y');
            //         $y = date('Y', strtotime('-1 year'));
            //     }
            //     $from_date = $y . "-04-01" . ' 00:00:00';
            //     $to_date = $pt . "-03-31" . ' 23:59:59';
            //     return $query->whereBetween('created_at', [$from_date, $to_date]);
            // })
            ->columns([
                TextColumn::make('id')->searchable(),
                TextColumn::make('customer_id')->searchable(),
                TextColumn::make('sap_customer_id')->searchable()->label('SFDC Id'),
                TextColumn::make('first_name')->searchable(),
                TextColumn::make('last_name')->searchable(),
                TextColumn::make('mobile_number')->searchable(),
                TextColumn::make('email')->label('Email')->searchable(),

                TextColumn::make('hospital_names')
                    ->label('Hospitals')
                    ->wrap()
                    ->getStateUsing(function ($record) {
                        $hospitals = Hospitals::where('customer_id', $record->id)->get();
                        return $hospitals->isNotEmpty()
                            ? implode(', ', $hospitals->pluck('hospital_name')->toArray())
                            : '-';
                    }),

                TextColumn::make('hospital_departments')
                    ->label('Departments')
                    ->wrap()
                    ->getStateUsing(function ($record) {
                        $hospitals = Hospitals::where('customer_id', $record->id)->get();
                        if ($hospitals->isEmpty()) return '-';

                        $allDepartments = [];
                        foreach ($hospitals as $hospital) {
                            $deptIds = explode(',', $hospital->dept_id);
                            $departments = Departments::whereIn('id', $deptIds)->pluck('name')->toArray();
                            $allDepartments = array_merge($allDepartments, $departments);
                        }
                        return implode(', ', array_unique($allDepartments));
                    }),

                TextColumn::make('hospital_cities')
                    ->label('City')
                    ->wrap()
                    ->getStateUsing(fn($record) =>
                        ($cities = Hospitals::where('customer_id', $record->id)->pluck('city')->toArray())
                            ? implode(', ', $cities) : '-'
                    ),

                TextColumn::make('hospital_states')
                    ->label('State')
                    ->wrap()
                    ->getStateUsing(fn($record) =>
                        ($states = Hospitals::where('customer_id', $record->id)->pluck('state')->toArray())
                            ? implode(', ', $states) : '-'
                    ),

                TextColumn::make('otp_code')->numeric()->sortable(),
                IconColumn::make('is_verified')->boolean(),
                TextColumn::make('created_at')->dateTime()->sortable(),
            ])
            ->defaultSort('id', 'desc')
            ->modifyQueryUsing(fn (Builder $query) => $query->where('email', 'like', '%olympus.com%'))

            // ✅ Date Range Filter
            ->filters([
                TrashedFilter::make(),
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
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                // BulkActionGroup::make([
                //     DeleteBulkAction::make(),
                //     ForceDeleteBulkAction::make(),
                //     RestoreBulkAction::make(),
                // ]),
            ])
            ->headerActions([
                Action::make('export_all')
                    ->label('Export All Data')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('primary')
                    ->action(function ($livewire) {
                        $from_date = $livewire->tableFilters['created_at']['created_from'] ?? null;
                        $to_date = $livewire->tableFilters['created_at']['created_until'] ?? null;

                        // Default financial year
                        $month = date('m');
                        if ($month >= 4) {
                            $y = date('Y');
                            $pt = date('Y', strtotime('+1 year'));
                        } else {
                            $pt = date('Y');
                            $y = date('Y', strtotime('-1 year'));
                        }
                        $from_date = $from_date ? $from_date . ' 00:00:00' : $y . "-04-01 00:00:00";
                        $to_date   = $to_date ? $to_date . ' 23:59:59' : $pt . "-03-31 23:59:59";

                        // Get all customers with date filter
                        $customers = \App\Models\Customers::where('email', 'like', '%olympus.com%')
                            ->whereBetween('created_at', [$from_date, $to_date])
                            ->get();

                        $filename = 'olympus_customers_' . now()->format('Y_m_d_H_i_s') . '.xlsx';

                        return Excel::download(new \App\Exports\OlympusCustomersExport($customers), $filename);
                    }),
            ]);
    }
}
