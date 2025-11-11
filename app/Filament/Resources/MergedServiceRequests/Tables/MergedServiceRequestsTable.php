<?php

namespace App\Filament\Resources\MergedServiceRequests\Tables;

use App\Filament\Exports\MergedServiceRequestExporter;
use App\Models\ArchiveServiceRequests;
use App\Models\ServiceRequests;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ExportAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class MergedServiceRequestsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
//                TextColumn::make('id')
//                    ->label('ID')
//                    ->numeric()
//                    ->sortable(),
                TextColumn::make('cvm_id')->label('My Voice ID')
                    ->searchable(),
                TextColumn::make('customer.first_name')->label('First Name')
                    ->searchable(),
                TextColumn::make('customer.last_name')->label('Last Name')
                    ->searchable(),
                TextColumn::make('hospital.hospital_name')->label('Hospital Name')
                    ->searchable(),
                TextColumn::make('departmentData.name')->label('Department Name')
                    ->searchable(),
                TextColumn::make('hospital.city')->label('City')
                    ->searchable(),
                TextColumn::make('hospital.state')->label('State')
                    ->searchable(),
                TextColumn::make('employeeData.name')->label('Employee Name')
                    ->searchable(),
                TextColumn::make('request_type')
                    ->searchable(),
                TextColumn::make('sub_type')
                    ->searchable(),
                TextColumn::make('status')
                    ->searchable(),
                TextColumn::make('remarks'),
                TextColumn::make('last_updated_by')->label('Last Updated By'),
                TextColumn::make('created_at')->label('Created At')->dateTime()->sortable(),
                TextColumn::make('updated_at')->label('Updated At')->dateTime()->sortable(),

                TextColumn::make('source')
                    ->searchable(),
            ])->defaultSort('id', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'Received' => 'Received',
                        'Assigned' => 'Assigned',
                        'Closed' => 'Closed',
                        'Escalated' => 'Escalated',
                    ]),

                Filter::make('created_at')
                    ->form([
                        DatePicker::make('created_from'),
                        DatePicker::make('created_until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];

                        if ($data['created_from'] ?? null) {
                            $indicators['created_from'] = 'Created from ' . \Carbon\Carbon::parse($data['created_from'])->toFormattedDateString();
                        }

                        if ($data['created_until'] ?? null) {
                            $indicators['created_until'] = 'Created until ' . \Carbon\Carbon::parse($data['created_until'])->toFormattedDateString();
                        }

                        return $indicators;
                    }),

                SelectFilter::make('source')
                    ->label('Source')
                    ->options([
                        'active' => 'Active',
                        'archive' => 'Archive',
                    ])->default('active'),

            ])
            ->recordActions([
                ViewAction::make(),

                EditAction::make()
                    ->url(fn ($record) =>
                    $record->source === 'active'
                        ? route('filament.admin.resources.service-requests.edit', ['record' => $record->id])
                        : route('filament.admin.resources.archive-service-requests.edit', ['record' => $record->id])
                    ),

//                    ->openUrlInNewTab(),

               Action::make('Delete')
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        if ($record->source === 'active') {
                            ServiceRequests::find($record->id)?->delete();
                        } else {
                            ArchiveServiceRequests::find($record->id)?->delete();
                        }
                    })
                    ->color('danger')
                    ->icon('heroicon-o-trash'),

                Action::make('showHistory')
                    ->label('View Status History')
                    ->icon('heroicon-o-clock')
                    ->modalHeading('Request Status List')
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Close')
                    ->modalContent(fn ($record) => view('filament.modals.request-history', [
                        'history' => $record->statusTimelineData,
                    ])),

            ])
            ->headerActions([
                ExportAction::make()
                    ->exporter(MergedServiceRequestExporter::class)
//                    ->modifyQueryUsing(function (Builder $query, array $data) {
//                        $latestTapMeIds = TapMe::join('users', 'tap_mes.user_id', '=', 'users.id')
//                            ->selectRaw('MAX(tap_mes.id) as id')
//                            ->groupBy('users.mobile')
//                            ->pluck('id')
//                            ->unique();
//
//                        return $query
//                            ->whereIn('id', $latestTapMeIds)
//                            ->when(
//                                $data['created_from'] ?? null,
//                                fn (Builder $query, $date) => $query->whereDate('created_at', '>=', $date),
//                            )
//                            ->when(
//                                $data['created_until'] ?? null,
//                                fn (Builder $query, $date) => $query->whereDate('created_at', '<=', $date),
//                            )
//                            ->with([
//                                'user' => fn ($q) => $q->withTrashed(),
//                                'tapMeHistory.store',
//                                'tapMeHistory.order',
//                            ]);
//                    })
            ])

            ->toolbarActions([
//                BulkActionGroup::make([
//                    DeleteBulkAction::make(),
//                ]),
            ]);
    }
}
