<?php

namespace App\Filament\Resources\DirectRequests\Pages;

use App\Filament\Resources\DirectRequests\DirectRequestResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditDirectRequest extends EditRecord
{
    protected static string $resource = DirectRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
