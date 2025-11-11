<?php

namespace App\Filament\Resources\Feedback\Tables;

use App\Models\Feedback;
use App\Models\ServiceRequests;
use App\Models\ArchiveServiceRequests;
use App\Models\Customers;
use App\Models\Hospitals;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FeedbackTable
{
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
            ->modifyQueryUsing(function (Builder $query) {
                return $query
                    ->leftJoin('service_requests', 'feedback.request_id', '=', 'service_requests.id')
                    ->leftJoin('archive_service_requests', 'feedback.request_id', '=', 'archive_service_requests.id')
                    ->leftJoin('customers', function ($join) {
                        $join->on('customers.id', '=', DB::raw('COALESCE(service_requests.customer_id, archive_service_requests.customer_id)'));
                    })
                    ->select(
                        'feedback.*',
                        'customers.first_name as first_name',
                        'customers.last_name as last_name',
                        DB::raw('COALESCE(service_requests.request_type, archive_service_requests.request_type) as request_type'),
                        DB::raw('COALESCE(service_requests.sub_type, archive_service_requests.sub_type) as sub_type')
                    );
            })
            ->columns([
                TextColumn::make('id')
                    ->label('ID'),

                TextColumn::make('request_id')
                    ->label('Request ID')
                    ->searchable(),

                TextColumn::make('response_speed')
                    ->label('Response Speed')
                    ->formatStateUsing(fn ($state) => self::renderStars($state))
                    ->html(),

                TextColumn::make('quality_of_response')
                    ->label('Quality of Response')
                    ->formatStateUsing(fn ($state) => self::renderStars($state))
                    ->html(),

                TextColumn::make('app_experience')
                    ->label('App Experience')
                    ->formatStateUsing(fn ($state) => self::renderStars($state))
                    ->html(),

                TextColumn::make('olympus_staff_performance')
                    ->label('Olympus Staff Performance')
                    ->formatStateUsing(fn ($state) => self::renderStars($state))
                    ->html(),

                TextColumn::make('remarks')
                    ->label('Remarks')
                    ->wrap(),

                TextColumn::make('first_name')
                    ->label('First Name'),

                TextColumn::make('last_name')
                    ->label('Last Name'),

                TextColumn::make('hospitals')
                    ->label('Hospitals')
                    ->wrap()
                    ->getStateUsing(function ($record) {
                        $hospitals = self::getHospitals($record); // returns array of hospital models
                        if (empty($hospitals)) return '-';
                        return implode(', ', array_map(fn($h) => $h->hospital_name ?? '-', $hospitals));
                    }),

                TextColumn::make('departments')
                    ->label('Departments')
                    ->wrap()
                    ->getStateUsing(function ($record) {
                        $hospitals = self::getHospitals($record);
                        if (empty($hospitals)) return '-';
                        $allDepartments = [];
                        foreach ($hospitals as $hospital) {
                            $deptIds = explode(',', $hospital->dept_id);
                            $departments = \App\Models\Departments::whereIn('id', $deptIds)->pluck('name')->toArray();
                            $allDepartments = array_merge($allDepartments, $departments);
                        }
                        $allDepartments = array_unique($allDepartments);
                        return implode(', ', $allDepartments) ?: '-';
                    }),

                TextColumn::make('city')
                    ->label('City')
                    ->wrap()
                    ->getStateUsing(function ($record) {
                        $hospitals = self::getHospitals($record);
                        if (empty($hospitals)) return '-';
                        $cities = array_map(fn($h) => $h->city ?? '-', $hospitals);
                        return implode(', ', array_unique($cities));
                    }),

                TextColumn::make('state')
                    ->label('State')
                    ->wrap()
                    ->getStateUsing(function ($record) {
                        $hospitals = self::getHospitals($record);
                        if (empty($hospitals)) return '-';
                        $states = array_map(fn($h) => $h->state ?? '-', $hospitals);
                        return implode(', ', array_unique($states));
                    }),

                TextColumn::make('request_type')
                    ->label('Request Type'),

                TextColumn::make('sub_type')
                ->label('Sub Type'),


                TextColumn::make('created_at')
                    ->label('Created At')
                    ->dateTime('d M Y h:i a')
                    ->sortable(),

                TextColumn::make('updated_at')
                    ->label('Updated At')
                    ->dateTime('d M Y h:i a')
                    ->sortable(),
            ])
            ->defaultSort('id', 'desc')
            // ✅ Date Range Filter
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
                                $q->whereDate('feedback.created_at', '>=', $data['created_from'])
                                  ->whereDate('feedback.created_at', '<=', $data['created_until']);
                            })
                            ->when(!$data['created_from'] && !$data['created_until'], function ($q) use ($from_date, $to_date) {
                                $q->whereDate('feedback.created_at', '>=', $from_date)
                                  ->whereDate('feedback.created_at', '<=', $to_date);
                            });
                    }),
            ])
            //, layout: Tables\Enums\FiltersLayout::AboveContent)

            ->recordActions([
                ViewAction::make(),
                //EditAction::make(),
            ])

            ->toolbarActions([
                // BulkActionGroup::make([
                //     DeleteBulkAction::make(),
                // ]),
            ])->headerActions([
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

                        // Get all feedback records with relationships
                        $feedbacks = Feedback::with(['ServiceRequestData', 'ArchiveServiceRequestData'])
                            ->whereBetween('created_at', [$from_date, $to_date])
                            ->get();

                        $filename = 'feedback_export_all_' . now()->format('Y_m_d_H_i_s') . '.xlsx';

                        return Excel::download(new \App\Exports\FeedbackExport($feedbacks), $filename);
                    }),
            ]);

    }

    // ⭐ Render stars
    protected static function renderStars($state): string
    {
        $stars = '';
        $rating = (int) $state;
        for ($i = 1; $i <= 5; $i++) {
            $stars .= $i <= $rating
                ? '<span class="text-yellow-500">★</span>'
                : '<span class="text-gray-300">☆</span>';
        }
        return $stars;
    }

    // 🔹 Get customer from ServiceRequests or ArchiveServiceRequests
    private static function getCustomer($record)
    {
        $serviceRequest = ServiceRequests::find($record->request_id)
            ?? ArchiveServiceRequests::find($record->request_id);

        return $serviceRequest ? Customers::find($serviceRequest->customer_id) : null;
    }

    // 🔹 Get hospitals for a given request
    private static function getHospitals($record): array
    {
        $serviceRequest = ServiceRequests::find($record->request_id)
            ?? ArchiveServiceRequests::find($record->request_id);

        if (!$serviceRequest) {
            return [];
        }
        // dd($serviceRequest);
        $hospitalIds = explode(',',$serviceRequest->hospital_id);
        // Adjust relationship or logic as per your DB
        return Hospitals::whereIn('id', $hospitalIds)->get()->all();
    }

}
