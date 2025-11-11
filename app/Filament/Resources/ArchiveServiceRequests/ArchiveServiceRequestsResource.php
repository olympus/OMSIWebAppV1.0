<?php

namespace App\Filament\Resources\ArchiveServiceRequests;

use App\Filament\Resources\ArchiveServiceRequests\Pages\CreateArchiveServiceRequests;
use App\Filament\Resources\ArchiveServiceRequests\Pages\EditArchiveServiceRequests;
use App\Filament\Resources\ArchiveServiceRequests\Pages\ListArchiveServiceRequests;
use App\Filament\Resources\ArchiveServiceRequests\Pages\ViewArchiveServiceRequests;
use App\Filament\Resources\ArchiveServiceRequests\Schemas\ArchiveServiceRequestsForm;
use App\Filament\Resources\ArchiveServiceRequests\Schemas\ArchiveServiceRequestsInfolist;
use App\Filament\Resources\ArchiveServiceRequests\Tables\ArchiveServiceRequestsTable;
use App\Models\ArchiveServiceRequests;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ArchiveServiceRequestsResource extends Resource
{
    protected static ?string $model = ArchiveServiceRequests::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'request_type';

    public static function form(Schema $schema): Schema
    {
        return ArchiveServiceRequestsForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return ArchiveServiceRequestsInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ArchiveServiceRequestsTable::configure($table);
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
            'index' => ListArchiveServiceRequests::route('/'),
            'create' => CreateArchiveServiceRequests::route('/create'),
            'view' => ViewArchiveServiceRequests::route('/{record}'),
            'edit' => EditArchiveServiceRequests::route('/{record}/edit'),
        ];
    }


    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

}
