<?php

namespace App\Filament\Resources\ApiRequests\Pages;

use App\Filament\Resources\ApiRequests\ApiRequestsResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListApiRequests extends ListRecords
{
    protected static string $resource = ApiRequestsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            //CreateAction::make(),
        ];
    }

    public function getTitle(): string
    {
        return 'Latest Api Activity';
    }
}
