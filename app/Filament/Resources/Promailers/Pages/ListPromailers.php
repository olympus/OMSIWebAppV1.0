<?php

namespace App\Filament\Resources\Promailers\Pages;

use App\Filament\Resources\Promailers\PromailerResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPromailers extends ListRecords
{
    protected static string $resource = PromailerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
