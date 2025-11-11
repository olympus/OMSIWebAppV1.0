<?php

namespace App\Filament\Resources\ApiRequests\Pages;

use App\Filament\Resources\ApiRequests\ApiRequestsResource;
use Filament\Resources\Pages\CreateRecord;

class CreateApiRequests extends CreateRecord
{
    protected static string $resource = ApiRequestsResource::class;
}
