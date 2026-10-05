<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use App\Filament\Widgets\WestDashboardStats;
use App\Filament\Widgets\WestCustomerTitleChart;
use App\Filament\Widgets\WestCustomerTrendChart;
use App\Filament\Widgets\WestDepartmentChart;
use App\Filament\Widgets\WestDepartmentEscalationChart;
use App\Filament\Widgets\WestDepartmentStatusChart;
use App\Filament\Widgets\WestDepartmentTrendChart;
use App\Filament\Widgets\WestEscalationChart;
use App\Filament\Widgets\WestFeedbackChart;
use App\Filament\Widgets\WestHospitalDepartmentChart;
use App\Filament\Widgets\WestRequestTrendChart;
use App\Filament\Widgets\WestRequestTypeChart;
use App\Filament\Widgets\WestStatusChart;
use App\Filament\Widgets\WestTatChart;

class WestDashboard extends Page
{
    protected static ?string $navigationLabel = 'West';
    protected static ?string $slug = 'dashboard/west';
    protected static string|\UnitEnum|null $navigationGroup = 'Dashboard';
    protected static ?string $navigationParentItem = 'Home';
    protected static ?string $title = 'West Dashboard';
    protected static string|null|\BackedEnum $navigationIcon = Heroicon::OutlinedHome;
    protected static ?int $navigationSort = 4;

    protected string $view = 'filament.pages.west-dashboard';

    protected function getHeaderWidgets(): array
    {
        return [
            WestDashboardStats::class,
            WestCustomerTitleChart::class,
            WestCustomerTrendChart::class,
            WestDepartmentChart::class,
            WestDepartmentEscalationChart::class,
            WestDepartmentStatusChart::class,
            WestDepartmentTrendChart::class,
            WestEscalationChart::class,
            WestFeedbackChart::class,
            WestHospitalDepartmentChart::class,
            WestRequestTrendChart::class,
            WestRequestTypeChart::class,
            WestStatusChart::class,
            WestTatChart::class,
        ];
    }
}
