<?php

namespace App\Filament\Resources\Feedback\Pages;

use App\Filament\Resources\Feedback\FeedbackResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewFeedback extends ViewRecord
{
    protected static string $resource = FeedbackResource::class;

    // Optional: used for breadcrumbs
    protected static ?string $recordTitleAttribute = 'request_id';

    // protected function getTitle(): ?string
    // {
    //     $requestId = $this->record->request_id ?? null;
    //     return $requestId ? "Feedback for Request ID - {$requestId}" : null;
    // }

    public function getHeading(): string
    {
        // Returns a dynamic heading like "Edit: Customer Name"
                $requestId = $this->record->request_id ?? null;
        return $requestId ? "Feedback for Request ID - {$requestId}" : null;
    }

    protected function getHeaderActions(): array
    {
        return [
            //EditAction::make(),
        ];
    }
}
