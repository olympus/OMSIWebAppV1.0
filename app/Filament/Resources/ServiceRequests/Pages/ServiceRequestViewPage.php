<?php

namespace App\Filament\Resources\ServiceRequests\Pages;

use App\Filament\Resources\ServiceRequests\ServiceRequestsResource;
use App\Models\ServiceRequests;
use App\Models\ArchiveServiceRequests;
use App\Models\CombinedServiceRequests;
use App\StatusTimeline;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Database\Eloquent\Model;

class ServiceRequestViewPage extends ViewRecord
{
    protected static string $resource = ServiceRequestsResource::class;
    protected string $view = 'filament.pages.service-request-view-page';

    public $history;

    // ✅ Correct signature (non-nullable)
    public function getRecord(): Model
    {
        // Use findOrFail so it always returns a Model
        return ServiceRequests::findOrFail($this->record?->id ?? 0);
    }

    public function mount(string|int $recordId): void
    {
        parent::mount($recordId);

        $this->history = StatusTimeline::where('request_id', $recordId)
            ->orderBy('created_at', 'desc')
            ->get();
    }
}
