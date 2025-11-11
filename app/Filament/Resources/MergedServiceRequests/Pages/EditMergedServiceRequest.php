<?php

namespace App\Filament\Resources\MergedServiceRequests\Pages;

use App\Filament\Resources\MergedServiceRequests\MergedServiceRequestResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditMergedServiceRequest extends EditRecord
{
    protected static string $resource = MergedServiceRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
