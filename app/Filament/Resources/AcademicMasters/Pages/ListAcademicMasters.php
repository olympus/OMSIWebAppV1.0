<?php

namespace App\Filament\Resources\AcademicMasters\Pages;

use App\Filament\Resources\AcademicMasters\AcademicMasterResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListAcademicMasters extends ListRecords
{
    protected static string $resource = AcademicMasterResource::class;

    protected function getHeaderActions(): array
    {
        return [
            //CreateAction::make(),
        ];
    }
}
