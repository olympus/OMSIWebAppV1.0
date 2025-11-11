<?php

namespace App\Filament\Resources\ServiceRequestData\Tables;

use App\Filament\Exports\CombinedServiceRequestsExporter;

use App\Models\ServiceRequests;
use App\Models\ArchiveServiceRequests;
use App\Models\CombinedServiceRequests; // ✅ Make sure this model exists (even if empty)
use App\StatusTimeline;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Actions\ExportAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class ServiceRequestDataTable
{
    public static function configure(Table $table): Table
    {
        return $table
            // ->query(function (Builder $query): Builder {
            //     $search = request()->get('tableSearch');
            //     $status = request()->query('status');

            //     // ✅ Base query for active
            //     $active = ServiceRequests::query()
            //         ->select(
            //             'id',
            //             'cvm_id',
            //             'customer_id',
            //             'hospital_id',
            //             'dept_id',
            //             'employee_code',
            //             'request_type',
            //             'sub_type',
            //             'status',
            //             'remarks',
            //             'last_updated_by',
            //             'created_at',
            //             'updated_at'
            //         )
            //         ->where('request_type', 'like', '%service%')
            //         ->when($status, fn($q) => $q->where('status', $status)) // ✅ exact match
            //         ->when($search, fn($q) =>
            //             $q->where(function ($inner) use ($search) {
            //                 $inner->where('cvm_id', 'like', "%{$search}%")
            //                     ->orWhere('remarks', 'like', "%{$search}%")
            //                     ->orWhere('last_updated_by', 'like', "%{$search}%");
            //             })
            //         );

            //     // ✅ Base query for archived
            //     $archived = ArchiveServiceRequests::query()
            //         ->select(
            //             'id',
            //             'cvm_id',
            //             'customer_id',
            //             'hospital_id',
            //             'dept_id',
            //             'employee_code',
            //             'request_type',
            //             'sub_type',
            //             'status',
            //             'remarks',
            //             'last_updated_by',
            //             'created_at',
            //             'updated_at'
            //         )
            //         ->where('request_type', 'like', '%service%')
            //         ->when($status, fn($q) => $q->where('status', $status)) // ✅ exact match
            //         ->when($search, fn($q) =>
            //             $q->where(function ($inner) use ($search) {
            //                 $inner->where('cvm_id', 'like', "%{$search}%")
            //                     ->orWhere('remarks', 'like', "%{$search}%")
            //                     ->orWhere('last_updated_by', 'like', "%{$search}%");
            //             })
            //         );

            //     // ✅ Combine both using unionAll
            //     $union = $active->unionAll($archived);

            //     // Wrap in Eloquent builder (Filament requires a Builder instance)
            //     $builder = DB::query()
            //         ->fromSub($union, 'service_requests_union')
            //         ->select('*')
            //         ->when($status, fn($q) => $q->where('status', $status)) // optional, applied again
            //         ->orderByDesc('created_at')
            //         ->orderBy('id', 'asc'); // ✅ Use 'id' from union, not CombinedServiceRequests

            //     return $builder; 
            // })


            ->columns([
                TextColumn::make('cvm_id')->label('My Voice ID')->searchable(),
                TextColumn::make('customer.first_name')->label('First Name')->searchable(),
                TextColumn::make('customer.last_name')->label('Last Name')->searchable(),
                TextColumn::make('hospital.hospital_name')->label('Hospital Name')->searchable(),
                TextColumn::make('departmentData.name')->label('Department Name')->searchable(),
                TextColumn::make('hospital.city')->label('City')->searchable(),
                TextColumn::make('hospital.state')->label('State')->searchable(),
                TextColumn::make('employeeData.name')->label('Employee Name')->searchable(),
                TextColumn::make('request_type')->label('Request Type')->searchable(),
                TextColumn::make('sub_type')->label('Sub Type')->searchable(),
                TextColumn::make('status')->label('Status')->badge(),
                TextColumn::make('remarks')->label('Remarks'),
                TextColumn::make('last_updated_by')->label('Last Updated By'),
                TextColumn::make('created_at')->label('Created At')->dateTime()->sortable(),
                TextColumn::make('updated_at')->label('Updated At')->dateTime()->sortable(),
            ])
            ->defaultSort('created_at', 'desc')

            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'Received' => 'Received',
                        'Assigned' => 'Assigned',
                        'Re-assigned' => 'Re Assigned',
                        'Attended' => 'Attended',
                        'Received_At_Repair_Center' => 'Received At Repair Center',
                        'Quotation_Prepared' => 'Quotation Prepared',
                        'PO_Received' => 'PO Received',
                        'Repair_Started' => 'Repair Started',
                        'Repair_Completed' => 'Repair Completed',
                        'Ready_To_Dispatch' => 'Ready To Dispatch',
                        'Dispatched' => 'Dispatched',
                        'Closed' => 'Closed',
                    ])
                    ->default(request()->get('status')), // take from URL when menu clicked
                // Filter::make('status')
                //     ->query(fn(Builder $query) =>
                //         $query->when(request('status'),
                //             fn($q) => $q->where('status', 'like', '%' . request('status') . '%'))
                //     ),

                Filter::make('created_at')
                    ->form([
                        DatePicker::make('created_from'),
                        DatePicker::make('created_until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['created_from'],
                                fn(Builder $query, $date) => $query->whereDate('created_at', '>=', $date))
                            ->when($data['created_until'],
                                fn(Builder $query, $date) => $query->whereDate('created_at', '<=', $date));
                    }),
            ])

            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                // Action::make('viewCustom')
                //     ->label('View Details')
                //     ->url(fn($record) => route('service-requests.view', ['recordId' => $record->id]))
                //     ->icon('heroicon-o-eye'),

                // EditAction::make()
                //     ->label('Edit')
                //     ->url(fn($record) =>
                //         route('filament.admin.resources.service-requests.edit', ['record' => $record->id]))
                //     ->icon('heroicon-o-pencil-square'),

                Action::make('Delete')
                    ->requiresConfirmation()
                    ->label('Delete')
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->action(fn($record) => [
                        ServiceRequests::find($record->id)?->delete(),
                        ArchiveServiceRequests::find($record->id)?->delete(),
                    ]),

                Action::make('showHistory')
                    ->label('View Status History')
                    ->icon('heroicon-o-clock')
                    ->modalHeading('Request Status List')
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Close')
                    ->modalContent(fn($record) =>
                        view('filament.modals.request-history', [
                            'history' => StatusTimeline::where('request_id',$record->id)->get(),
                        ])),
            ])
            ->headerActions([
                ExportAction::make()
                    ->exporter(CombinedServiceRequestsExporter::class) 
            ]);
    }
}
