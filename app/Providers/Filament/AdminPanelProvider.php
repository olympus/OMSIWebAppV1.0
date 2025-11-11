<?php

namespace App\Providers\Filament;

use App\Filament\Pages\CheckEmail;
use App\Filament\Pages\SapImport;
use App\Filament\Pages\EsasImport;
use App\Filament\Pages\ArchiveDataFilter;
use App\Filament\Pages\EastDashboard;
use App\Filament\Pages\WestDashboard;
use App\Filament\Pages\NorthDashboard;
use App\Filament\Pages\SouthDashboard;
use App\Filament\Widgets\DashboardStats;
use App\Filament\Widgets\RequestChart;
use App\Filament\Widgets\UserRegistrationChart;
use App\Filament\Widgets\FeedbackDepartmentChart;
use App\Filament\Widgets\FeedbackGeographicChart;
use App\Filament\Widgets\DirectMyVoiceCallChart;
use App\Filament\Widgets\DirectRequestTrendChart;
use App\Filament\Widgets\AverageTatReportChart;
use App\Filament\Widgets\EscalationReasonTrendChart;
use App\Filament\Widgets\EscalationReasonSummaryChart;
use Filament\Http\Middleware\Authenticate;
use BezhanSalleh\FilamentShield\FilamentShieldPlugin;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use App\Filament\Pages\Home;
use App\Filament\Pages\RegionalDashboard;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets\AccountWidget;
use Filament\Widgets\FilamentInfoWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use App\Http\Middleware\RestrictIpAddresses;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel 
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->passwordReset()
            ->homeUrl('/merged-service-requests')

            ->databaseNotifications()
            ->colors([
                'primary' => Color::Amber,
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            // Resource registration removed; Filament will auto-discover resources in app/Filament/Resources
            ->pages([
                \Filament\Pages\Dashboard::class,
                CheckEmail::class,
                SapImport::class,
                EsasImport::class,
                ArchiveDataFilter::class,
                EastDashboard::class,
                WestDashboard::class,
                NorthDashboard::class,
                SouthDashboard::class, 
            ])
            ->widgets([
                DashboardStats::class,
                RequestChart::class,
                UserRegistrationChart::class,
                FeedbackDepartmentChart::class,
                FeedbackGeographicChart::class,
                DirectMyVoiceCallChart::class,
                DirectRequestTrendChart::class,
                AverageTatReportChart::class,
                EscalationReasonTrendChart::class,
                EscalationReasonSummaryChart::class, 
                
                \App\Filament\Widgets\EastDashboardStats::class,
                \App\Filament\Widgets\EastCustomerTitleChart::class,
                \App\Filament\Widgets\EastCustomerTrendChart::class,
                \App\Filament\Widgets\EastDepartmentChart::class,
                \App\Filament\Widgets\EastDepartmentEscalationChart::class,
                \App\Filament\Widgets\EastDepartmentStatusChart::class,
                \App\Filament\Widgets\EastDepartmentTrendChart::class,
                \App\Filament\Widgets\EastEscalationChart::class,
                \App\Filament\Widgets\EastFeedbackChart::class,
                \App\Filament\Widgets\EastHospitalDepartmentChart::class,
                \App\Filament\Widgets\EastRequestTrendChart::class,
                \App\Filament\Widgets\EastRequestTypeChart::class,
                \App\Filament\Widgets\EastStatusChart::class,
                \App\Filament\Widgets\EastTatChart::class,


                \App\Filament\Widgets\WestDashboardStats::class,
                \App\Filament\Widgets\WestCustomerTitleChart::class,
                \App\Filament\Widgets\WestCustomerTrendChart::class,
                \App\Filament\Widgets\WestDepartmentChart::class,
                \App\Filament\Widgets\WestDepartmentEscalationChart::class,
                \App\Filament\Widgets\WestDepartmentStatusChart::class,
                \App\Filament\Widgets\WestDepartmentTrendChart::class,
                \App\Filament\Widgets\WestEscalationChart::class,
                \App\Filament\Widgets\WestFeedbackChart::class,
                \App\Filament\Widgets\WestHospitalDepartmentChart::class,
                \App\Filament\Widgets\WestRequestTrendChart::class,
                \App\Filament\Widgets\WestRequestTypeChart::class,
                \App\Filament\Widgets\WestStatusChart::class,
                \App\Filament\Widgets\WestTatChart::class,

                \App\Filament\Widgets\NorthDashboardStats::class,
                \App\Filament\Widgets\NorthCustomerTitleChart::class,
                \App\Filament\Widgets\NorthCustomerTrendChart::class,
                \App\Filament\Widgets\NorthDepartmentChart::class,
                \App\Filament\Widgets\NorthDepartmentEscalationChart::class,
                \App\Filament\Widgets\NorthDepartmentStatusChart::class,
                \App\Filament\Widgets\NorthDepartmentTrendChart::class,
                \App\Filament\Widgets\NorthEscalationChart::class,
                \App\Filament\Widgets\NorthFeedbackChart::class,
                \App\Filament\Widgets\NorthHospitalDepartmentChart::class,
                \App\Filament\Widgets\NorthRequestTrendChart::class,
                \App\Filament\Widgets\NorthRequestTypeChart::class,
                \App\Filament\Widgets\NorthStatusChart::class,
                \App\Filament\Widgets\NorthTatChart::class,

                \App\Filament\Widgets\SouthDashboardStats::class,
                \App\Filament\Widgets\SouthCustomerTitleChart::class,
                \App\Filament\Widgets\SouthCustomerTrendChart::class,
                \App\Filament\Widgets\SouthDepartmentChart::class,
                \App\Filament\Widgets\SouthDepartmentEscalationChart::class,
                \App\Filament\Widgets\SouthDepartmentStatusChart::class,
                \App\Filament\Widgets\SouthDepartmentTrendChart::class,
                \App\Filament\Widgets\SouthEscalationChart::class,
                \App\Filament\Widgets\SouthFeedbackChart::class,
                \App\Filament\Widgets\SouthHospitalDepartmentChart::class,
                \App\Filament\Widgets\SouthRequestTrendChart::class,
                \App\Filament\Widgets\SouthRequestTypeChart::class,
                \App\Filament\Widgets\SouthStatusChart::class,
                \App\Filament\Widgets\SouthTatChart::class,

//                AccountWidget::class,
//                FilamentInfoWidget::class,
            ])
            ->middleware([
                RestrictIpAddresses::class,
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->plugins([
                FilamentShieldPlugin::make(),
            ])
            ->authMiddleware([
                Authenticate::class,
                RestrictIpAddresses::class, // ✅ Add here also for security
            ]);
    }
}
