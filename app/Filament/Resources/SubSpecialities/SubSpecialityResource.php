<?php

namespace App\Filament\Resources\SubSpecialities;

use App\Filament\Resources\SubSpecialities\Pages\CreateSubSpeciality;
use App\Filament\Resources\SubSpecialities\Pages\EditSubSpeciality;
use App\Filament\Resources\SubSpecialities\Pages\ListSubSpecialities;
use App\Filament\Resources\SubSpecialities\Pages\ViewSubSpeciality;
use App\Filament\Resources\SubSpecialities\Schemas\SubSpecialityForm;
use App\Filament\Resources\SubSpecialities\Schemas\SubSpecialityInfolist;
use App\Filament\Resources\SubSpecialities\Tables\SubSpecialitiesTable;
use App\Models\SubSpeciality;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SubSpecialityResource extends Resource
{
    protected static ?string $model = SubSpeciality::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'sub_specialities_name';

    public static function form(Schema $schema): Schema
    {
        return SubSpecialityForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return SubSpecialityInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SubSpecialitiesTable::configure($table);
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
            'index' => ListSubSpecialities::route('/'),
            'create' => CreateSubSpeciality::route('/create'),
            'view' => ViewSubSpeciality::route('/{record}'),
            'edit' => EditSubSpeciality::route('/{record}/edit'),
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
