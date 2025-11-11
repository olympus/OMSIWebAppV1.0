<?php

namespace App\Filament\Resources\ArchiveServiceRequests\Pages;

use App\Filament\Resources\ArchiveServiceRequests\ArchiveServiceRequestsResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewArchiveServiceRequests extends ViewRecord
{
    protected static string $resource = ArchiveServiceRequestsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
