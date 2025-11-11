<?php

namespace App\Filament\Resources\ProductInfos\Pages;

use App\Filament\Resources\ProductInfos\ProductInfoResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListProductInfos extends ListRecords
{
    protected static string $resource = ProductInfoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            //CreateAction::make(),
        ];
    }
}
