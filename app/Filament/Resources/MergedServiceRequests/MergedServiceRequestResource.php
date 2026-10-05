<?php

namespace App\Filament\Resources\MergedServiceRequests;

use App\Filament\Resources\MergedServiceRequests\Pages\CreateMergedServiceRequest;
use App\Filament\Resources\MergedServiceRequests\Pages\EditMergedServiceRequest;
use App\Filament\Resources\MergedServiceRequests\Pages\ListMergedServiceRequests;
use App\Filament\Resources\MergedServiceRequests\Pages\ViewMergedServiceRequest;
use App\Filament\Resources\MergedServiceRequests\Schemas\MergedServiceRequestForm;
use App\Filament\Resources\MergedServiceRequests\Schemas\MergedServiceRequestInfolist;
use App\Filament\Resources\MergedServiceRequests\Tables\MergedServiceRequestsTable;
use App\Models\MergedServiceRequest;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema as SchemaFacade;
use UnitEnum;

class MergedServiceRequestResource extends Resource
{
    protected static ?string $model = MergedServiceRequest::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-viewfinder-circle';

    protected static ?string $recordTitleAttribute = 'request_type';
    
    protected static string | UnitEnum | null $navigationGroup = 'Requests';

    protected static ?int $navigationSort = 1;

    //protected static ?int $navigationGroupSort = 1;
    
    protected static ?string $navigationLabel = 'All Requests';


    public static function form(Schema $schema): Schema
    {
        return MergedServiceRequestForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return MergedServiceRequestInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MergedServiceRequestsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListMergedServiceRequests::route('/'),
           // 'create' => CreateMergedServiceRequest::route('/create'),
            'view' => ViewMergedServiceRequest::route('/{record}'),
            'edit' => EditMergedServiceRequest::route('/{record}/edit'),
        ];
    }

    /**
     * The admin "All Requests" screen expects rows from merged_service_requests (DB view).
     * That view is often missing in local DBs; build the same result from active + archive tables.
     */
    public static function getEloquentQuery(): Builder
    {
        $serviceTable = 'service_requests';
        $archiveTable = 'archive_service_requests';

        if (! SchemaFacade::hasTable($serviceTable) || ! SchemaFacade::hasTable($archiveTable)) {
            return parent::getEloquentQuery();
        }

        $serviceCols = SchemaFacade::getColumnListing($serviceTable);
        $archiveCols = SchemaFacade::getColumnListing($archiveTable);

        $allCols = array_values(array_unique(array_merge($serviceCols, $archiveCols)));
        sort($allCols);

        $allCols = array_values(array_filter($allCols, static fn (string $name): bool => (bool) preg_match('/^[a-zA-Z0-9_]+$/', $name)));

        $selectSql = static function (string $table, array $tableCols) use ($allCols): string {
            $segments = [];
            foreach ($allCols as $col) {
                $segments[] = in_array($col, $tableCols, true)
                    ? "{$table}.`{$col}`"
                    : "NULL as `{$col}`";
            }

            return implode(', ', $segments);
        };

        $union = DB::table($serviceTable)
            ->selectRaw($selectSql($serviceTable, $serviceCols).", 'active' as `source`")
            ->unionAll(
                DB::table($archiveTable)->selectRaw($selectSql($archiveTable, $archiveCols).", 'archive' as `source`")
            );

        return MergedServiceRequest::query()->fromSub($union, 'merged_service_requests');
    }

    public static function getNavigationBadge(): ?string
    {
        $count = static::getEloquentQuery()->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'info';
    }

//    public static function getNavigationItems(): array
//    {
//        return [
//            NavigationItem::make('All Requests')
//                ->icon('heroicon-o-rectangle-stack')
//                ->url(static::getUrl('index'))
//                ->badge(fn () => (string) MergedServiceRequest::count()), // ✅ correct method
//
//            NavigationItem::make('Pending Requests')
//                ->icon('heroicon-o-clock')
//                ->url(static::getUrl('index', ['status' => 'pending']))
//                ->badge(fn () => (string) MergedServiceRequest::where('source', 'archive')->count())
//               , // ✅ correct method
//        ];
//    }




}
