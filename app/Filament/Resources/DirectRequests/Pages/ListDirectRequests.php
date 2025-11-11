<?php

namespace App\Filament\Resources\DirectRequests\Pages;

use App\Filament\Resources\DirectRequests\DirectRequestResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListDirectRequests extends ListRecords
{
    protected static string $resource = DirectRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // CreateAction::make(),
        ];
    }
}
