<?php

namespace App\Filament\Resources\EmployeeTeams\Pages;

use App\Filament\Resources\EmployeeTeams\EmployeeTeamResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditEmployeeTeam extends EditRecord
{
    protected static string $resource = EmployeeTeamResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // ViewAction::make(),
            //DeleteAction::make(),
        ];
    }
}
