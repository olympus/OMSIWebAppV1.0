<?php

namespace App\Filament\Resources\AcademicMasters\Pages;

use App\Filament\Resources\AcademicMasters\AcademicMasterResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewAcademicMaster extends ViewRecord
{
    protected static string $resource = AcademicMasterResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
