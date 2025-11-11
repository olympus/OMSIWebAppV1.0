<?php

namespace App\Filament\Resources\ArchiveServiceRequests\Pages;

use App\Filament\Resources\ArchiveServiceRequests\ArchiveServiceRequestsResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListArchiveServiceRequests extends ListRecords
{
    protected static string $resource = ArchiveServiceRequestsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
