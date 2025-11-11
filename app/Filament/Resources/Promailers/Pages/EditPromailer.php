<?php

namespace App\Filament\Resources\Promailers\Pages;

use App\Filament\Resources\Promailers\PromailerResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPromailer extends EditRecord
{
    protected static string $resource = PromailerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            //DeleteAction::make(),
        ];
    }
}
