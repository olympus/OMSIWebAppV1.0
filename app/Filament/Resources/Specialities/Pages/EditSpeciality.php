<?php

namespace App\Filament\Resources\Specialities\Pages;

use App\Filament\Resources\Specialities\SpecialityResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditSpeciality extends EditRecord
{
    protected static string $resource = SpecialityResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            // DeleteAction::make(),
            // ForceDeleteAction::make(),
            // RestoreAction::make(),
        ];
    }
}
