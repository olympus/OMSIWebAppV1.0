<?php

namespace App\Filament\Resources\OlympusCustomers\Pages;

use App\Filament\Resources\OlympusCustomers\OlympusCustomerResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListOlympusCustomers extends ListRecords
{
    protected static string $resource = OlympusCustomerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            //CreateAction::make(),
        ];
    }
}
