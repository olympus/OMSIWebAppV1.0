<?php

namespace App\Filament\Resources\Videos\Pages;

use App\Filament\Resources\Videos\VideoResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditVideo extends EditRecord
{
    protected static string $resource = VideoResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        return VideoResource::normalizeOtherVideoFields($data);
    }

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
