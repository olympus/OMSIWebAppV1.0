<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class RegionalDashboardsHub extends Page
{
    protected static ?string $navigationLabel = 'Dashboards';

    protected static ?string $slug = 'dashboards';

    protected static string|\UnitEnum|null $navigationGroup = 'Dashboard';

    protected static ?int $navigationSort = 2;

    protected static string|null|\BackedEnum $navigationIcon = 'heroicon-o-presentation-chart-line';

    protected string $view = 'filament.pages.regional-dashboards-hub';
}
