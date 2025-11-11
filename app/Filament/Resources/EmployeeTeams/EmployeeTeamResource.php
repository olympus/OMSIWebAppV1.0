<?php

namespace App\Filament\Resources\EmployeeTeams;

use App\Filament\Resources\EmployeeTeams\Pages\CreateEmployeeTeam;
use App\Filament\Resources\EmployeeTeams\Pages\EditEmployeeTeam;
use App\Filament\Resources\EmployeeTeams\Pages\ListEmployeeTeams;
use App\Filament\Resources\EmployeeTeams\Pages\ViewEmployeeTeam;
use App\Filament\Resources\EmployeeTeams\Schemas\EmployeeTeamForm;
use App\Filament\Resources\EmployeeTeams\Schemas\EmployeeTeamInfolist;
use App\Filament\Resources\EmployeeTeams\Tables\EmployeeTeamsTable;
use App\Models\EmployeeTeam;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class EmployeeTeamResource extends Resource
{
    protected static ?string $model = EmployeeTeam::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return EmployeeTeamForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return EmployeeTeamInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return EmployeeTeamsTable::configure($table);
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
            'index' => ListEmployeeTeams::route('/'),
            'create' => CreateEmployeeTeam::route('/create'),
            'view' => ViewEmployeeTeam::route('/{record}'),
            'edit' => EditEmployeeTeam::route('/{record}/edit'),
        ];
    }
}
