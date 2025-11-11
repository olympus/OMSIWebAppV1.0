<?php

namespace App\Filament\Resources\DirectRequests\Pages;

use App\Filament\Resources\DirectRequests\DirectRequestResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewDirectRequest extends ViewRecord
{
    protected static string $resource = DirectRequestResource::class;
    protected static ?string $recordTitleAttribute = 'request_id';

    protected function getHeaderActions(): array
    {
        return [
            // EditAction::make(),
        ];
    }

    public function getHeading(): string
    {
        // Returns a dynamic heading like "Edit: Customer Name"
                $requestId = $this->record->id ?? null;
        return $requestId ? "Direct Request ID - {$requestId}" : null;
    }
}
