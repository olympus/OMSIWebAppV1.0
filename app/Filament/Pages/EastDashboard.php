<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use App\Filament\Widgets\EastDashboardStats;
use App\Filament\Widgets\EastCustomerTitleChart;
use App\Filament\Widgets\EastCustomerTrendChart;
use App\Filament\Widgets\EastDepartmentChart;
use App\Filament\Widgets\EastDepartmentEscalationChart;
use App\Filament\Widgets\EastDepartmentStatusChart;
use App\Filament\Widgets\EastDepartmentTrendChart;
use App\Filament\Widgets\EastEscalationChart;
use App\Filament\Widgets\EastFeedbackChart;
use App\Filament\Widgets\EastHospitalDepartmentChart;
use App\Filament\Widgets\EastRequestTrendChart;
use App\Filament\Widgets\EastRequestTypeChart;
use App\Filament\Widgets\EastStatusChart;
use App\Filament\Widgets\EastTatChart;

class EastDashboard extends Page
{
    protected static ?string $navigationLabel = 'East';
    protected static ?string $slug = 'dashboard/east';
    protected static string|\UnitEnum|null $navigationGroup = 'Dashboard';
    protected static ?string $navigationParentItem = 'Home';
    protected static ?string $title = 'East Dashboard';
    protected static string|null|\BackedEnum $navigationIcon = Heroicon::OutlinedHome;
    protected static ?int $navigationSort = 3;

    protected string $view = 'filament.pages.east-dashboard';

    protected function getHeaderWidgets(): array
    {
        return [
            EastDashboardStats::class,
            EastCustomerTitleChart::class,
            EastCustomerTrendChart::class,
            EastDepartmentChart::class,
            EastDepartmentEscalationChart::class,
            EastDepartmentStatusChart::class,
            EastDepartmentTrendChart::class,
            EastEscalationChart::class,
            EastFeedbackChart::class,
            EastHospitalDepartmentChart::class,
            EastRequestTrendChart::class,
            EastRequestTypeChart::class,
            EastStatusChart::class,
            EastTatChart::class,
        ];
    }
}
