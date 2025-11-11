<?php

namespace App\Filament\Resources\ApiRequests\Pages;

use App\Filament\Resources\ApiRequests\ApiRequestsResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditApiRequests extends EditRecord
{
    protected static string $resource = ApiRequestsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // ViewAction::make(),
            // DeleteAction::make(),
        ];
    }
}
