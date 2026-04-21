<?php

namespace App\Filament\Resources\RoiCalculators;

use App\Filament\Resources\RoiCalculators\Pages\CreateRoiCalculator;
use App\Filament\Resources\RoiCalculators\Pages\EditRoiCalculator;
use App\Filament\Resources\RoiCalculators\Pages\ListRoiCalculators;
use App\Filament\Resources\RoiCalculators\Pages\ViewRoiCalculator;
use App\Filament\Resources\RoiCalculators\Schemas\RoiCalculatorForm;
use App\Filament\Resources\RoiCalculators\Schemas\RoiCalculatorInfolist;
use App\Filament\Resources\RoiCalculators\Tables\RoiCalculatorsTable;
use App\Models\RoiCalculator;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class RoiCalculatorResource extends Resource
{
    protected static ?string $model = RoiCalculator::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return RoiCalculatorForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return RoiCalculatorInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return RoiCalculatorsTable::configure($table);
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
            'index' => ListRoiCalculators::route('/'),
            //'create' => CreateRoiCalculator::route('/create'),
            'view' => ViewRoiCalculator::route('/{record}'),
            //'edit' => EditRoiCalculator::route('/{record}/edit'),
        ];
    }
}
