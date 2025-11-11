<?php

namespace App\Filament\Resources\ServiceRequests;

use App\Filament\Resources\ServiceRequests\Pages\CreateServiceRequests;
use App\Filament\Resources\ServiceRequests\Pages\EditServiceRequests;
use App\Filament\Resources\ServiceRequests\Pages\ListServiceRequests;
use App\Filament\Resources\ServiceRequests\Pages\ViewServiceRequests;
use App\Filament\Resources\ServiceRequests\Pages\ServiceRequestViewPage;
use App\Filament\Resources\ServiceRequests\Schemas\ServiceRequestsForm;
use App\Filament\Resources\ServiceRequests\Schemas\ServiceRequestsInfolist;
use App\Filament\Resources\ServiceRequests\Tables\ServiceRequestsTable;
use App\Models\ServiceRequests;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ServiceRequestsResource extends Resource
{
    protected static ?string $model = ServiceRequests::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'request_type';

    public static function form(Schema $schema): Schema
    {
        return ServiceRequestsForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return ServiceRequestsInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ServiceRequestsTable::configure($table);
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
            'index' => ListServiceRequests::route('/'),
            'create' => CreateServiceRequests::route('/create'),
            'view' => ServiceRequestViewPage::route('/{record}'),
            'edit' => EditServiceRequests::route('/{record}/edit'),
        ];
    }


    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

}
