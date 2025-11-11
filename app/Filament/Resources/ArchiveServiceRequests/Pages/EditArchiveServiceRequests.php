<?php

namespace App\Filament\Resources\ArchiveServiceRequests\Pages;

use App\Filament\Resources\ArchiveServiceRequests\ArchiveServiceRequestsResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditArchiveServiceRequests extends EditRecord
{
    protected static string $resource = ArchiveServiceRequestsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
