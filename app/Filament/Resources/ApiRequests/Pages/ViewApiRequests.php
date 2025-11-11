<?php

namespace App\Filament\Resources\ApiRequests\Pages;

use App\Filament\Resources\ApiRequests\ApiRequestsResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewApiRequests extends ViewRecord
{
    protected static string $resource = ApiRequestsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            //EditAction::make(),
        ];
    }
}
