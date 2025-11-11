<?php

namespace App\Filament\Resources\ServiceRequestData\Pages;

use App\Filament\Resources\ServiceRequestData\ServiceRequestDataResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewServiceRequestData extends ViewRecord
{
    protected static string $resource = ServiceRequestDataResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
