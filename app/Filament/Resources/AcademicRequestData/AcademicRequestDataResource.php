<?php

namespace App\Filament\Resources\AcademicRequestData;

use App\Filament\Resources\AcademicRequestData\Pages\CreateAcademicRequestData;
use App\Filament\Resources\AcademicRequestData\Pages\EditAcademicRequestData;
use App\Filament\Resources\AcademicRequestData\Pages\ListAcademicRequestData;
use App\Filament\Resources\AcademicRequestData\Pages\ViewAcademicRequestData;
use App\Filament\Resources\AcademicRequestData\Schemas\AcademicRequestDataForm;
use App\Filament\Resources\AcademicRequestData\Schemas\AcademicRequestDataInfolist;
use App\Filament\Resources\AcademicRequestData\Tables\AcademicRequestDataTable;
use App\Models\ArchiveServiceRequests;
use App\Models\CombinedServiceRequests;
use App\Models\ServiceRequests;
use BackedEnum;
use Filament\Navigation\NavigationItem;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Auth\Access\Response;
use Illuminate\Database\Eloquent\Model;

class AcademicRequestDataResource extends Resource
{
    private const PORTAL_PERMISSION = 'academic_requests_portal_access';

    protected static ?string $model = CombinedServiceRequests::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-academic-cap';

    public static function getAuthorizationResponse(string $action, ?Model $record = null): Response
    {
        if (static::shouldSkipAuthorization()) {
            return Response::allow();
        }

        $user = auth()->user();

        return $user?->can(self::PORTAL_PERMISSION)
            ? Response::allow()
            : Response::deny();
    }

    public static function getNavigationItems(): array
    {
        $statuses = [
            'Received',
            'Assigned',
            'Attended',
            'Closed',
            'All Requests',
        ];

        $items = [];
        $sort = 1;

        foreach ($statuses as $status) {
            if ($status === 'Received') {
                $show_status = 'Received';
                $status = 'Received';
            } elseif ($status === 'Assigned') {
                $show_status = 'Assigned';
                $status = 'Assigned';
            } elseif ($status === 'Attended') {
                $show_status = 'Attended';
                $status = 'Attended';
            } elseif ($status === 'Closed') {
                $show_status = 'Closed';
                $status = 'Closed';
            } elseif ($status === 'All Requests') {
                $show_status = 'All Requests';
                $status = '';
            }

            $activeCount = ServiceRequests::where('request_type', 'like', '%academic%')
                ->when($status, function ($q) use ($status) {
                    $q->where('status', $status);
                })->count();

            $archiveCount = ArchiveServiceRequests::where('request_type', 'like', '%academic%')
                ->when($status, function ($q) use ($status) {
                    $q->where('status', $status);
                })->count();

            $totalCount = $activeCount + $archiveCount;

            $items[] = NavigationItem::make("{$show_status} ({$totalCount})")
                ->icon('heroicon-o-clipboard-document-list')
                ->group('Academic Requests')
                ->sort($sort++)
                ->url(static::getUrl('index', array_filter(['status' => $status])))
                ->badge($totalCount > 0 ? (string) $totalCount : null);
        }

        return $items;
    }

    public static function form(Schema $schema): Schema
    {
        return AcademicRequestDataForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AcademicRequestDataTable::configure($table);
    }

    public static function infolist(Schema $schema): Schema
    {
        return AcademicRequestDataInfolist::configure($schema);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAcademicRequestData::route('/'),
            'view' => ViewAcademicRequestData::route('/{record}'),
            'create' => CreateAcademicRequestData::route('/create'),
            'edit' => EditAcademicRequestData::route('/{record}/edit'),
        ];
    }
}
