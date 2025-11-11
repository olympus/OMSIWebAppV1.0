<?php

namespace App\Filament\Resources\ServiceMasters\Pages;

use App\Filament\Resources\ServiceMasters\ServiceMasterResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewServiceMaster extends ViewRecord
{
    protected static string $resource = ServiceMasterResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
