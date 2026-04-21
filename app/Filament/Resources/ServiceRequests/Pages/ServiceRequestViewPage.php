<?php

namespace App\Filament\Resources\ServiceRequests\Pages;

use App\Filament\Resources\ServiceRequests\ServiceRequestsResource;
use App\StatusTimeline;
use Filament\Resources\Pages\ViewRecord;

class ServiceRequestViewPage extends ViewRecord
{
    protected static string $resource = ServiceRequestsResource::class;

    protected string $view = 'filament.pages.service-request-view-page';

    public $history;

    public function mount($record): void
    {
        parent::mount($record);

        $this->history = StatusTimeline::where('request_id', $this->record->id)
            ->latest()
            ->get();
    }
}