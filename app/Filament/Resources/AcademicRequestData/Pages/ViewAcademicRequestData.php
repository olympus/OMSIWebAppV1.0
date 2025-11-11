<?php

namespace App\Filament\Resources\AcademicRequestData\Pages;

use App\Filament\Resources\AcademicRequestData\AcademicRequestDataResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewAcademicRequestData extends ViewRecord
{
    protected static string $resource = AcademicRequestDataResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
