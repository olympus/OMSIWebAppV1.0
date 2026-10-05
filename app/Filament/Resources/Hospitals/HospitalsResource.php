<?php

namespace App\Filament\Resources\Hospitals;

use App\Filament\Resources\Hospitals\Pages\CreateHospitals;
use App\Filament\Resources\Hospitals\Pages\EditHospitals;
use App\Filament\Resources\Hospitals\Pages\ListHospitals;
use App\Filament\Resources\Hospitals\Pages\ViewHospitals;
use App\Filament\Resources\Hospitals\Schemas\HospitalsForm;
use App\Filament\Resources\Hospitals\Schemas\HospitalsInfolist;
use App\Filament\Resources\Hospitals\Tables\HospitalsTable;
use App\Models\Hospitals;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class HospitalsResource extends Resource
{
    protected static ?string $model = Hospitals::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingOffice2;

    protected static ?string $recordTitleAttribute = 'hospital_name';

    protected static string|\UnitEnum|null $navigationGroup = 'Others';

    protected static ?int $navigationSort = 1;

    public static function getNavigationBadge(): ?string
    {
        $count = Hospitals::query()->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'info';
    }

    public static function form(Schema $schema): Schema
    {
        return HospitalsForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return HospitalsInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return HospitalsTable::configure($table);
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
            'index' => ListHospitals::route('/'),
            // 'create' => CreateHospitals::route('/create'),
            'view' => ViewHospitals::route('/{record}'),
            'edit' => EditHospitals::route('/{record}/edit'),
        ];
    }
}
