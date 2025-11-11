<?php

namespace App\Filament\Resources\AutoEmails\Pages;

use App\Filament\Resources\AutoEmails\AutoEmailsResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditAutoEmails extends EditRecord
{
    protected static string $resource = AutoEmailsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
