<?php

namespace App\Filament\Resources\EmployeeTeams\Pages;

use App\Filament\Resources\EmployeeTeams\EmployeeTeamResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewEmployeeTeam extends ViewRecord
{
    protected static string $resource = EmployeeTeamResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
