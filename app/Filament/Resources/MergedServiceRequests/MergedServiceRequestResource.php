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
use Filament\Navigation\NavigationGroup;
use Filament\Navigation\NavigationItem;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class MergedServiceRequestResource extends Resource
{
    protected static ?string $model = MergedServiceRequest::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'request_type';
    protected static ?int $navigationSort = 1;

    protected static string | UnitEnum | null $navigationGroup = 'Requests';
    protected static ?int $navigationGroupSort = 1;



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

    public static function getNavigationBadge(): ?string
    {
        // Example: show pending order count
        return (string) MergedServiceRequest::where('source','active')->count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning'; // success, danger, primary, etc.
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
