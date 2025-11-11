<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use App\Filament\Widgets\SouthDashboardStats;
use App\Filament\Widgets\SouthCustomerTitleChart;
use App\Filament\Widgets\SouthCustomerTrendChart;
use App\Filament\Widgets\SouthDepartmentChart;
use App\Filament\Widgets\SouthDepartmentEscalationChart;
use App\Filament\Widgets\SouthDepartmentStatusChart;
use App\Filament\Widgets\SouthDepartmentTrendChart;
use App\Filament\Widgets\SouthEscalationChart;
use App\Filament\Widgets\SouthFeedbackChart;
use App\Filament\Widgets\SouthHospitalDepartmentChart;
use App\Filament\Widgets\SouthRequestTrendChart;
use App\Filament\Widgets\SouthRequestTypeChart;
use App\Filament\Widgets\SouthStatusChart;
use App\Filament\Widgets\SouthTatChart;

class SouthDashboard extends Page
{
    protected static ?string $navigationLabel = 'South';
    protected static ?string $slug = 'dashboard/south';
    protected static string|\UnitEnum|null $navigationGroup = 'Home';
    protected static ?string $title = 'South Dashboard';
    protected static string|null|\BackedEnum $navigationIcon = 'heroicon-o-home';
    protected static ?int $navigationSort = 2;
    protected static ?int $navigationGroupSort = 5;

    protected string $view = 'filament.pages.south-dashboard';

    protected function getHeaderWidgets(): array
    {
        return [
            SouthDashboardStats::class,
            SouthCustomerTitleChart::class,
            SouthCustomerTrendChart::class,
            SouthDepartmentChart::class,
            SouthDepartmentEscalationChart::class,
            SouthDepartmentStatusChart::class,
            SouthDepartmentTrendChart::class,
            SouthEscalationChart::class,
            SouthFeedbackChart::class,
            SouthHospitalDepartmentChart::class,
            SouthRequestTrendChart::class,
            SouthRequestTypeChart::class,
            SouthStatusChart::class,
            SouthTatChart::class,
        ];
    }
}
