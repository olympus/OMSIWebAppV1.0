<?php

namespace App\Filament\Resources\ServiceMasters\Pages;

use App\Filament\Resources\ServiceMasters\ServiceMasterResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditServiceMaster extends EditRecord
{
    protected static string $resource = ServiceMasterResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // ViewAction::make(),
            // DeleteAction::make(),
        ];
    }
}
