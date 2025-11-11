<?php

namespace App\Filament\Resources\Hospitals\Tables;

use App\Models\Hospitals;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Actions\DeleteAction;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Symfony\Component\HttpFoundation\StreamedResponse;

class HospitalsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                 TextColumn::make('id') 
                    ->sortable(),
                TextColumn::make('hospital_name')
                    ->label('Hospital Name')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('dept_id')
                    ->label('Dept Id')
                    ->sortable()
                    ->searchable(),
                
                TextColumn::make('address')
                    ->label('Address')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('city')
                    ->label('City')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('state')
                    ->label('State')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('zip')
                    ->label('Zip')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('country')
                    ->label('Country')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('customer_id')
                    ->label('Customer Id')
                    ->sortable()
                    ->searchable(),
                
                // TextColumn::make('responsible_branch')
                //     ->label('Responsible Branch')
                //  ->sortable() 

                TextColumn::make('customer_id')
                    ->label('Customer ID')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('created_at')
                    ->label('Created At')
                    ->dateTime('d M Y h:i a')
                    ->sortable(),
                    // ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->label('Updated At')
                    ->dateTime('d M Y h:i a')
                    ->sortable(),
                    // ->toggleable(isToggledHiddenByDefault: true),
            ])

            ->filters([
                // You can later add filters here if needed
            ])

            ->recordActions([
                // ViewAction::make(),
                // EditAction::make(),
                DeleteAction::make(),
            ])

            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),

                // ✅ Export Filtered Data
                Action::make('export')
                    ->label('Export Data')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('primary')
                    ->action(function ($livewire): StreamedResponse {
                        // Get filtered data (if filters are added later)
                        $query = $livewire->getFilteredTableQuery();
                        $hospitals = $query->get();

                        $filename = 'hospitals_export_' . now()->format('Y_m_d_H_i_s') . '.csv';
                        $headers = [
                            'Content-Type' => 'text/csv',
                            'Content-Disposition' => "attachment; filename=\"$filename\"",
                        ];

                        $callback = function () use ($hospitals) {
                            $handle = fopen('php://output', 'w');

                            // CSV Header
                            fputcsv($handle, [
                                'Hospital Name',
                                'Dept ID',
                                'Dept Name',
                                'Address',
                                'City',
                                'State',
                                'Zip',
                                'Country',
                                'Responsible Branch',
                                'Customer ID',
                                'Created At',
                                'Updated At',
                            ]);

                            // Data Rows
                            foreach ($hospitals as $hospital) {
                                $dept_ids = explode(',', $hospital->dept_id);
                                $departments = \App\Models\Departments::whereIn('id', $dept_ids)->pluck('name')->all();
                                $depart_names = implode(', ', $departments);
                                fputcsv($handle, [
                                    $hospital->hospital_name,
                                    $hospital->dept_id,
                                    $depart_names,
                                    $hospital->address,
                                    $hospital->city,
                                    $hospital->state,
                                    $hospital->zip,
                                    $hospital->country,
                                    $hospital->responsible_branch,
                                    $hospital->customer_id,
                                    $hospital->created_at,
                                    $hospital->updated_at,
                                ]);
                            }

                            fclose($handle);
                        };

                        return response()->stream($callback, 200, $headers);
                    }),
            ]);
    }
}
