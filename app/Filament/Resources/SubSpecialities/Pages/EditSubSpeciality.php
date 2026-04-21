<?php

namespace App\Filament\Resources\SubSpecialities\Pages;

use App\Filament\Resources\SubSpecialities\SubSpecialityResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditSubSpeciality extends EditRecord
{
    protected static string $resource = SubSpecialityResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
