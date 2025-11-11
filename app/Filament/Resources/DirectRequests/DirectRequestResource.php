<?php

namespace App\Filament\Resources\DirectRequests;

use App\Filament\Resources\DirectRequests\Pages\CreateDirectRequest;
use App\Filament\Resources\DirectRequests\Pages\EditDirectRequest;
use App\Filament\Resources\DirectRequests\Pages\ListDirectRequests;
use App\Filament\Resources\DirectRequests\Pages\ViewDirectRequest;
use App\Filament\Resources\DirectRequests\Schemas\DirectRequestForm;
use App\Filament\Resources\DirectRequests\Schemas\DirectRequestInfolist;
use App\Filament\Resources\DirectRequests\Tables\DirectRequestsTable;
use App\Models\DirectRequest;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class DirectRequestResource extends Resource
{
    protected static ?string $model = DirectRequest::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'customer_name';

    public static function form(Schema $schema): Schema
    {
        return DirectRequestForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return DirectRequestInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return DirectRequestsTable::configure($table);
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
            'index' => ListDirectRequests::route('/'),
            'create' => CreateDirectRequest::route('/create'),
            'view' => ViewDirectRequest::route('/{record}'),
            'edit' => EditDirectRequest::route('/{record}/edit'),
        ];
    }
}
