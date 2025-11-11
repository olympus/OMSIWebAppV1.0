<?php

namespace App\Filament\Resources\AutoEmails\Pages;

use App\Filament\Resources\AutoEmails\AutoEmailsResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewAutoEmails extends ViewRecord
{
    protected static string $resource = AutoEmailsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
