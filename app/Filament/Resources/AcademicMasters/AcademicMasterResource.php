<?php

namespace App\Filament\Resources\AcademicMasters;

use App\Filament\Resources\AcademicMasters\Pages\CreateAcademicMaster;
use App\Filament\Resources\AcademicMasters\Pages\EditAcademicMaster;
use App\Filament\Resources\AcademicMasters\Pages\ListAcademicMasters;
use App\Filament\Resources\AcademicMasters\Pages\ViewAcademicMaster;
use App\Filament\Resources\AcademicMasters\Schemas\AcademicMasterForm;
use App\Filament\Resources\AcademicMasters\Schemas\AcademicMasterInfolist;
use App\Filament\Resources\AcademicMasters\Tables\AcademicMastersTable;
use App\Models\AcademicMaster;
use BackedEnum;
use UnitEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class AcademicMasterResource extends Resource
{
    protected static ?string $model = AcademicMaster::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'states';
    protected static string | UnitEnum | null $navigationGroup = 'Auto Emails';
    // protected static ?string $navigationGroup = 'Auto Emails';

    protected static ?int $navigationSort = 3;
    
    public static function form(Schema $schema): Schema
    {
        return AcademicMasterForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return AcademicMasterInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AcademicMastersTable::configure($table);
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
            'index' => ListAcademicMasters::route('/'),
            'create' => CreateAcademicMaster::route('/create'),
            'view' => ViewAcademicMaster::route('/{record}'),
            'edit' => EditAcademicMaster::route('/{record}/edit'),
        ];
    }
}
