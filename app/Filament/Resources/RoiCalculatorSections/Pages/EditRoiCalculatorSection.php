<?php

namespace App\Filament\Resources\RoiCalculatorSections\Pages;

use App\Filament\Resources\RoiCalculatorSections\RoiCalculatorSectionResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditRoiCalculatorSection extends EditRecord
{
    protected static string $resource = RoiCalculatorSectionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
