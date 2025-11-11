<?php

namespace App\Filament\Resources\OlympusCustomers\Pages;

use App\Filament\Resources\OlympusCustomers\OlympusCustomerResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewOlympusCustomer extends ViewRecord
{
    protected static string $resource = OlympusCustomerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
