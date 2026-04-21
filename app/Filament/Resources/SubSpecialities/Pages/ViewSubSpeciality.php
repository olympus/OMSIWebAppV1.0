<?php

namespace App\Filament\Resources\SubSpecialities\Pages;

use App\Filament\Resources\SubSpecialities\SubSpecialityResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewSubSpeciality extends ViewRecord
{
    protected static string $resource = SubSpecialityResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
