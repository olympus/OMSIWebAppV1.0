<?php

namespace App\Filament\Resources\RoiCalculators\Pages;

use App\Filament\Resources\RoiCalculators\RoiCalculatorResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewRoiCalculator extends ViewRecord
{
    protected static string $resource = RoiCalculatorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            //EditAction::make(),
        ];
    }
}
