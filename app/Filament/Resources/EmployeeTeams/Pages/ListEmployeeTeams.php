<?php

namespace App\Filament\Resources\EmployeeTeams\Pages;

use App\Filament\Resources\EmployeeTeams\EmployeeTeamResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListEmployeeTeams extends ListRecords
{
    protected static string $resource = EmployeeTeamResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
