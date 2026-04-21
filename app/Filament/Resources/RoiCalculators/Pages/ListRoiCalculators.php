<?php

namespace App\Filament\Resources\RoiCalculators\Pages;

use App\Filament\Resources\RoiCalculators\RoiCalculatorResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListRoiCalculators extends ListRecords
{
    protected static string $resource = RoiCalculatorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            //CreateAction::make(),
        ];
    }
}
