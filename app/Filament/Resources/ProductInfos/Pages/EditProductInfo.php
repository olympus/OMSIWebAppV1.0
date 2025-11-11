<?php

namespace App\Filament\Resources\ProductInfos\Pages;

use App\Filament\Resources\ProductInfos\ProductInfoResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditProductInfo extends EditRecord
{
    protected static string $resource = ProductInfoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
