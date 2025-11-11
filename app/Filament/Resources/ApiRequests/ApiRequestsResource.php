<?php

namespace App\Filament\Resources\ApiRequests;

use App\Filament\Resources\ApiRequests\Pages\CreateApiRequests;
use App\Filament\Resources\ApiRequests\Pages\EditApiRequests;
use App\Filament\Resources\ApiRequests\Pages\ListApiRequests;
use App\Filament\Resources\ApiRequests\Pages\ViewApiRequests;
use App\Filament\Resources\ApiRequests\Schemas\ApiRequestsForm;
use App\Filament\Resources\ApiRequests\Schemas\ApiRequestsInfolist;
use App\Filament\Resources\ApiRequests\Tables\ApiRequestsTable;
use App\Models\ApiRequests;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ApiRequestsResource extends Resource
{
    protected static ?string $model = ApiRequests::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'identifier';
    protected static ?string $navigationLabel = 'Api';

    public static function form(Schema $schema): Schema
    {
        return ApiRequestsForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return ApiRequestsInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ApiRequestsTable::configure($table);
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
            'index' => ListApiRequests::route('/'),
            'create' => CreateApiRequests::route('/create'),
            'view' => ViewApiRequests::route('/{record}'),
            'edit' => EditApiRequests::route('/{record}/edit'),
        ];
    }
}
