<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use App\Filament\Widgets\NorthDashboardStats;
use App\Filament\Widgets\NorthCustomerTitleChart;
use App\Filament\Widgets\NorthCustomerTrendChart;
use App\Filament\Widgets\NorthDepartmentChart;
use App\Filament\Widgets\NorthDepartmentEscalationChart;
use App\Filament\Widgets\NorthDepartmentStatusChart;
use App\Filament\Widgets\NorthDepartmentTrendChart;
use App\Filament\Widgets\NorthEscalationChart;
use App\Filament\Widgets\NorthFeedbackChart;
use App\Filament\Widgets\NorthHospitalDepartmentChart;
use App\Filament\Widgets\NorthRequestTrendChart;
use App\Filament\Widgets\NorthRequestTypeChart;
use App\Filament\Widgets\NorthStatusChart;
use App\Filament\Widgets\NorthTatChart;

class NorthDashboard extends Page
{
    protected static ?string $navigationLabel = 'North';
    protected static ?string $slug = 'dashboard/north';
    protected static string|\UnitEnum|null $navigationGroup = 'Home';
    protected static ?string $title = 'North Dashboard';
    protected static string|null|\BackedEnum $navigationIcon = 'heroicon-o-home';
    protected static ?int $navigationSort = 1;
    protected static ?int $navigationGroupSort = 5;

    protected string $view = 'filament.pages.north-dashboard';

    protected function getHeaderWidgets(): array
    {
        return [
            NorthDashboardStats::class,
            NorthCustomerTitleChart::class,
            NorthCustomerTrendChart::class,
            NorthDepartmentChart::class,
            NorthDepartmentEscalationChart::class,
            NorthDepartmentStatusChart::class,
            NorthDepartmentTrendChart::class,
            NorthEscalationChart::class,
            NorthFeedbackChart::class,
            NorthHospitalDepartmentChart::class,
            NorthRequestTrendChart::class,
            NorthRequestTypeChart::class,
            NorthStatusChart::class,
            NorthTatChart::class,
        ];
    }
}