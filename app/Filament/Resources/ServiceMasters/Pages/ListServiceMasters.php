<?php

namespace App\Filament\Resources\ServiceMasters\Pages;

use App\Filament\Resources\ServiceMasters\ServiceMasterResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListServiceMasters extends ListRecords
{
    protected static string $resource = ServiceMasterResource::class;

    protected function getHeaderActions(): array
    {
        return [
            //CreateAction::make(),
        ];
    }
}
