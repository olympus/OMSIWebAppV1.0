<?php

namespace App\Filament\Resources\AcademicMasters\Pages;

use App\Filament\Resources\AcademicMasters\AcademicMasterResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditAcademicMaster extends EditRecord
{
    protected static string $resource = AcademicMasterResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
