<?php

namespace App\Filament\Resources\Hospitals\Pages;

use App\Filament\Resources\Hospitals\HospitalsResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewHospitals extends ViewRecord
{
    protected static string $resource = HospitalsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
