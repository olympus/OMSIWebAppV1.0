<?php

namespace App\Filament\Resources\Hospitals\Pages;

use App\Filament\Resources\Hospitals\HospitalsResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditHospitals extends EditRecord
{
    protected static string $resource = HospitalsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
