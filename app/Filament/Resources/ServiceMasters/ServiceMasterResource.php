<?php

namespace App\Filament\Resources\ServiceMasters;

use App\Filament\Resources\ServiceMasters\Pages\CreateServiceMaster;
use App\Filament\Resources\ServiceMasters\Pages\EditServiceMaster;
use App\Filament\Resources\ServiceMasters\Pages\ListServiceMasters;
use App\Filament\Resources\ServiceMasters\Pages\ViewServiceMaster;
use App\Filament\Resources\ServiceMasters\Schemas\ServiceMasterForm;
use App\Filament\Resources\ServiceMasters\Schemas\ServiceMasterInfolist;
use App\Filament\Resources\ServiceMasters\Tables\ServiceMastersTable;
use App\Models\ServiceMaster;
use BackedEnum;
use UnitEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class ServiceMasterResource extends Resource
{
    protected static ?string $model = ServiceMaster::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-at-symbol';

    protected static ?string $recordTitleAttribute = 'states';

    protected static ?string $navigationLabel = 'Service Master';

    protected static string | UnitEnum | null $navigationGroup = 'Auto Emails';

    protected static ?int $navigationSort = 1;

    public static function getNavigationBadge(): ?string
    {
        $count = ServiceMaster::query()->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'info';
    }

    public static function form(Schema $schema): Schema
    {
        return ServiceMasterForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return ServiceMasterInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ServiceMastersTable::configure($table);
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
            'index' => ListServiceMasters::route('/'),
            'create' => CreateServiceMaster::route('/create'),
            'view' => ViewServiceMaster::route('/{record}'),
            'edit' => EditServiceMaster::route('/{record}/edit'),
        ];
    }
}
