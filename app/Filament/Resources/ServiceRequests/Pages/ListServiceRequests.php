<?php

namespace App\Filament\Resources\ServiceRequests\Pages;

use App\Filament\Resources\ServiceRequests\ServiceRequestsResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListServiceRequests extends ListRecords
{
    protected static string $resource = ServiceRequestsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
