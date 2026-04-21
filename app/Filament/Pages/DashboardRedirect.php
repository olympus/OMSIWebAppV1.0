<?php

namespace App\Filament\Pages;

use Filament\Pages\Dashboard;

class DashboardRedirect extends Dashboard
{
    public function mount(): void
    {
        $this->redirect('/admin/merged-service-requests');
    }

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }
}
