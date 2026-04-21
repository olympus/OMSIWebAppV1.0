<?php

namespace App\Filament\Resources\Specialities;

use App\Filament\Resources\Specialities\Pages\CreateSpeciality;
use App\Filament\Resources\Specialities\Pages\EditSpeciality;
use App\Filament\Resources\Specialities\Pages\ListSpecialities;
use App\Filament\Resources\Specialities\Pages\ViewSpeciality;
use App\Filament\Resources\Specialities\RelationManagers\SpecialityCategoriesRelationManager;
use App\Filament\Resources\Specialities\RelationManagers\SubSpecialitiesRelationManager;
use App\Filament\Resources\Specialities\RelationManagers\SubSubSpecialitiesRelationManager;
use App\Filament\Resources\Specialities\Schemas\SpecialityForm;
use App\Filament\Resources\Specialities\Schemas\SpecialityInfolist;
use App\Filament\Resources\Specialities\Tables\SpecialitiesTable;
use App\Models\Speciality;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SpecialityResource extends Resource
{
    protected static ?string $model = Speciality::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'specialities_name';

    public static function form(Schema $schema): Schema
    {
        return SpecialityForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return SpecialityInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SpecialitiesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            SubSpecialitiesRelationManager::class,
            //SubSubSpecialitiesRelationManager::class,
            SpecialityCategoriesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSpecialities::route('/'),
            'create' => CreateSpeciality::route('/create'),
            'view' => ViewSpeciality::route('/{record}'),
            'edit' => EditSpeciality::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
