<?php

namespace App\Filament\Resources\ProductInfos\Pages;

use App\Filament\Resources\ProductInfos\ProductInfoResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewProductInfo extends ViewRecord
{
    protected static string $resource = ProductInfoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // EditAction::make(),
        ];
    }
}
