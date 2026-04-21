<?php

namespace App\Filament\Resources\RoiCalculatorSections\Pages;

use App\Filament\Resources\RoiCalculatorSections\RoiCalculatorSectionResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListRoiCalculatorSections extends ListRecords
{
    protected static string $resource = RoiCalculatorSectionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
