<?php

namespace App\Filament\Resources\ServiceRequests\Pages;

use App\Filament\Resources\ServiceRequests\ServiceRequestsResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewServiceRequests extends ViewRecord
{
    protected static string $resource = ServiceRequestsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
