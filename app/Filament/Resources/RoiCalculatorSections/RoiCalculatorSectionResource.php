<?php

namespace App\Filament\Resources\RoiCalculatorSections;

use App\Filament\Resources\RoiCalculatorSections\Pages\CreateRoiCalculatorSection;
use App\Filament\Resources\RoiCalculatorSections\Pages\EditRoiCalculatorSection;
use App\Filament\Resources\RoiCalculatorSections\Pages\ListRoiCalculatorSections;
use App\Filament\Resources\RoiCalculatorSections\Pages\ViewRoiCalculatorSection;
use App\Filament\Resources\RoiCalculatorSections\Schemas\RoiCalculatorSectionForm;
use App\Filament\Resources\RoiCalculatorSections\Schemas\RoiCalculatorSectionInfolist;
use App\Filament\Resources\RoiCalculatorSections\Tables\RoiCalculatorSectionsTable;
use App\Models\RoiCalculatorSection;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class RoiCalculatorSectionResource extends Resource
{
    protected static ?string $model = RoiCalculatorSection::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Schema $schema): Schema
    {
        return RoiCalculatorSectionForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return RoiCalculatorSectionInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return RoiCalculatorSectionsTable::configure($table);
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
            'index' => ListRoiCalculatorSections::route('/'),
            'create' => CreateRoiCalculatorSection::route('/create'),
            'view' => ViewRoiCalculatorSection::route('/{record}'),
            'edit' => EditRoiCalculatorSection::route('/{record}/edit'),
        ];
    }
}
