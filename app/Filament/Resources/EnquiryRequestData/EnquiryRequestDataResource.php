<?php

namespace App\Filament\Resources\EnquiryRequestData;

use App\Filament\Resources\EnquiryRequestData\Pages\CreateEnquiryRequestData;
use App\Filament\Resources\EnquiryRequestData\Pages\EditEnquiryRequestData;
use App\Filament\Resources\EnquiryRequestData\Pages\ListEnquiryRequestData;
use App\Filament\Resources\EnquiryRequestData\Pages\ViewEnquiryRequestData;
use App\Filament\Resources\EnquiryRequestData\Schemas\EnquiryRequestDataForm;
use App\Filament\Resources\EnquiryRequestData\Schemas\EnquiryRequestDataInfolist;
use App\Filament\Resources\EnquiryRequestData\Tables\EnquiryRequestDataTable;
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

class EnquiryRequestDataResource extends Resource
{
    private const PORTAL_PERMISSION = 'enquiry_requests_portal_access';

    protected static ?string $model = CombinedServiceRequests::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-question-mark-circle';

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

            $activeCount = ServiceRequests::where('request_type', 'like', '%enquiry%')
                ->when($status, function ($q) use ($status) {
                    $q->where('status', $status);
                })->count();

            $archiveCount = ArchiveServiceRequests::where('request_type', 'like', '%enquiry%')
                ->when($status, function ($q) use ($status) {
                    $q->where('status', $status);
                })->count();

            $totalCount = $activeCount + $archiveCount;

            $items[] = NavigationItem::make("{$show_status} ({$totalCount})")
                ->icon('heroicon-o-clipboard-document-list')
                ->group('Enquiry Requests')
                ->sort($sort++)
                ->url(static::getUrl('index', array_filter(['status' => $status])))
                ->badge($totalCount > 0 ? (string) $totalCount : null);
        }

        return $items;
    }

    public static function form(Schema $schema): Schema
    {
        return EnquiryRequestDataForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return EnquiryRequestDataTable::configure($table);
    }

    public static function infolist(Schema $schema): Schema
    {
        return EnquiryRequestDataInfolist::configure($schema);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListEnquiryRequestData::route('/'),
            'view' => ViewEnquiryRequestData::route('/{record}'),
            'create' => CreateEnquiryRequestData::route('/create'),
            'edit' => EditEnquiryRequestData::route('/{record}/edit'),
        ];
    }
}
