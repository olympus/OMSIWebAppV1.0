<?php

namespace App\Filament\Resources\SubSpecialities\Pages;

use App\Filament\Resources\SubSpecialities\SubSpecialityResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListSubSpecialities extends ListRecords
{
    protected static string $resource = SubSpecialityResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
