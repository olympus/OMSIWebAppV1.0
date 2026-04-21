<?php

namespace App\Filament\Resources\RoiCalculators\Pages;

use App\Filament\Resources\RoiCalculators\RoiCalculatorResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditRoiCalculator extends EditRecord
{
    protected static string $resource = RoiCalculatorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            //DeleteAction::make(),
        ];
    }
}
