<?php

namespace App\Filament\Resources\AcademicMasters\Tables;

use App\Models\AcademicMaster;
use App\Models\AcademicMasters;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class AcademicMastersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function (Builder $query) {
                return $query->where('request_type', 'academic');
            })
            ->columns([
                 TextColumn::make('id') 
                    ->sortable(),
                TextColumn::make('states')
                    ->searchable()
                    ->sortable()
                    ->label('States'),
                TextColumn::make('departments')
                    ->searchable()
                    ->sortable()
                    ->label('Departments'),
                TextColumn::make('to_emails')
                    ->searchable()
                    ->sortable()
                    ->label('To Emails'),
                TextColumn::make('cc_emails')
                    ->searchable()
                    ->sortable()
                    ->label('CC Emails'),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->headerActions([
                Action::make('export_all')
                    ->label('Export All Data')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('success')
                    ->action(function () {
                        $records = AcademicMaster::where('request_type', 'academic')
                            ->orderBy('created_at', 'desc')
                            ->get();

                        if ($records->isEmpty()) {
                            \Filament\Notifications\Notification::make()
                                ->title('No data to export')
                                ->warning()
                                ->send();
                            return;
                        }

                        // Prepare CSV data
                        $exportData = $records->map(function ($record) {
                            return [
                                'ID' => $record->id,
                                'States' => $record->states,
                                'Departments' => $record->departments,
                                'To Emails' => $record->to_emails,
                                'CC Emails' => $record->cc_emails,
                                'Created At' => $record->created_at,
                                'Updated At' => $record->updated_at,
                            ];
                        });

                        $filename = 'academic_masters_' . now()->format('Y_m_d_H_i_s') . '.csv';
                        $filepath = storage_path("app/{$filename}");

                        $handle = fopen($filepath, 'w');
                        if ($exportData->isNotEmpty()) {
                            fputcsv($handle, array_keys($exportData->first()));
                        }
                        foreach ($exportData as $row) {
                            fputcsv($handle, $row);
                        }
                        fclose($handle);

                        return response()->download($filepath)->deleteFileAfterSend(true);
                    }),
            ]);
    }
}
