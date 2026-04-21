<?php

namespace App\Filament\Resources\RoiCalculatorSections\Pages;

use App\Filament\Resources\RoiCalculatorSections\RoiCalculatorSectionResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewRoiCalculatorSection extends ViewRecord
{
    protected static string $resource = RoiCalculatorSectionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
