<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\AverageTatReportChart;
use App\Filament\Widgets\DashboardStats;
use App\Filament\Widgets\DirectMyVoiceCallChart;
use App\Filament\Widgets\DirectRequestTrendChart;
use App\Filament\Widgets\EscalationReasonSummaryChart;
use App\Filament\Widgets\EscalationReasonTrendChart;
use App\Filament\Widgets\FeedbackDepartmentChart;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\Widget;

class AdminDashboard extends BaseDashboard
{
    protected static ?string $navigationLabel = 'Home';

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedHome;

    protected static string|\UnitEnum|null $navigationGroup = 'Dashboard';

    protected static ?int $navigationSort = 1;

    /**
     * Main Home dashboard only: summary tiles + non-regional charts.
     * Geographic (N/E/S/W) charts and regional page widgets stay on regional dashboard pages.
     *
     * @return array<class-string<Widget>>
     */
    public function getWidgets(): array
    {
        return [
            DashboardStats::class,
            FeedbackDepartmentChart::class,
            DirectMyVoiceCallChart::class,
            DirectRequestTrendChart::class,
            AverageTatReportChart::class,
            EscalationReasonTrendChart::class,
            EscalationReasonSummaryChart::class,
        ];
    }
}
