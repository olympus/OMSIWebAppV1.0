<?php

namespace App\Filament\Resources\MergedServiceRequests\Pages;

use App\Filament\Resources\MergedServiceRequests\MergedServiceRequestResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListMergedServiceRequests extends ListRecords
{
    protected static string $resource = MergedServiceRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
        //    CreateAction::make(),
        ];
    }
}
