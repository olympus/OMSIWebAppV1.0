<?php

namespace App\Filament\Resources\MergedServiceRequests\Pages;

use App\Filament\Resources\MergedServiceRequests\MergedServiceRequestResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewMergedServiceRequest extends ViewRecord
{
    protected static string $resource = MergedServiceRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
