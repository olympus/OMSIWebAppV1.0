<?php

namespace App\Filament\Widgets;

use App\Models\ServiceRequests;
use App\Models\ArchiveServiceRequests;
use Filament\Widgets\ChartWidget;
use Filament\Forms\Components\DatePicker;
use Filament\Schemas\Schema;
use Filament\Forms\Concerns\InteractsWithForms;
use Illuminate\Support\Carbon;
use Filament\Widgets\ChartWidget\Concerns\HasFiltersSchema;

class RequestChart extends ChartWidget
{
    use HasFiltersSchema, InteractsWithForms;

    protected ?string $heading = '1. Request Trend (Geographic)';

    protected int | string | array $columnSpan = 'full';

    protected ?string $maxHeight = '800px';

    protected bool $isCollapsible = true;

    protected static bool $isLazy = true;



    public function filtersSchema(Schema $schema): Schema
    {
        return $schema->components([
            DatePicker::make('startDate'),
            DatePicker::make('endDate'),
        ]);
    }
    // public function form(Schema $form): Schema
    // {
    //     return $form
    //         ->schema([
    //             DatePicker::make('fromDate')
    //                 ->label('From Date')
    //                 ->default($this->getDefaultFromDate())
    //                 ->live(),

    //             DatePicker::make('toDate')
    //                 ->label('To Date')
    //                 ->default($this->getDefaultToDate())
    //                 ->live(),
    //         ])
    //         ->columns(2)
    //         ->reactive();
    // }

    protected function getData(): array
    {
        $formData = $this->form->getState();
        $fromDate = $this->filters['startDate'] ?? $this->getDefaultFromDate();
        $toDate = $this->filters['endDate'] ?? $this->getDefaultToDate();

        // Get data for current service requests with region and month
        $currentRequests = ServiceRequests::selectRaw('DATE(created_at) as date, service_requests.*')
            ->where('is_practice', false)
            ->whereBetween('service_requests.created_at', [$fromDate, $toDate])
            ->with('hospital')
            ->get();

        // Get data for archived service requests with region and month
        $archivedRequests = ArchiveServiceRequests::select('*')
            ->selectRaw('DATE(created_at) as date')
            ->where('is_practice', false)
            ->whereBetween('created_at', [$fromDate, $toDate])
            ->with('hospital')
            ->get();

        // Group by month and region
        $monthlyRegionData = [];

        // Process current requests
        foreach ($currentRequests as $request) {
            $month = Carbon::parse($request->date)->format('M Y');
            $service_region = $this->find_region($request->hospital->state ?? '');
            $region = $service_region ?: 'Unknown';

            if (!isset($monthlyRegionData[$month])) {
                $monthlyRegionData[$month] = [];
            }
            if (!isset($monthlyRegionData[$month][$region])) {
                $monthlyRegionData[$month][$region] = 0;
            }
            $monthlyRegionData[$month][$region]++;
        }

        // Process archived requests
        foreach ($archivedRequests as $request) {
            $month = Carbon::parse($request->date)->format('M Y');
            $service_region = $this->find_region($request->hospital->state ?? '');
            $region = $service_region ?: 'Unknown';

            if (!isset($monthlyRegionData[$month])) {
                $monthlyRegionData[$month] = [];
            }
            if (!isset($monthlyRegionData[$month][$region])) {
                $monthlyRegionData[$month][$region] = 0;
            }
            $monthlyRegionData[$month][$region]++;
        }

        // Prepare datasets for each region
        $labels = array_keys($monthlyRegionData);
        $datasets = [];
        $regions = ['north', 'east', 'south', 'west', 'Unknown'];

        $colors = [
            'north' => ['rgba(54, 162, 235, 0.8)', 'rgba(54, 162, 235, 1)'],
            'east' => ['rgba(255, 99, 132, 0.8)', 'rgba(255, 99, 132, 1)'],
            'south' => ['rgba(75, 192, 192, 0.8)', 'rgba(75, 192, 192, 1)'],
            'west' => ['rgba(255, 205, 86, 0.8)', 'rgba(255, 205, 86, 1)'],
            'Unknown' => ['rgba(153, 102, 255, 0.8)', 'rgba(153, 102, 255, 1)'],
        ];

        foreach ($regions as $region) {
            $data = [];
            foreach ($labels as $month) {
                $data[] = $monthlyRegionData[$month][$region] ?? 0;
            }

            $datasets[] = [
                'label' => ucfirst($region),
                'data' => $data,
                'backgroundColor' => $colors[$region][0],
                'borderColor' => $colors[$region][1],
                'borderWidth' => 1,
            ];
        }

        $maxValue = 0;
        foreach ($regions as $region) {
            foreach ($labels as $month) {
                $value = $monthlyRegionData[$month][$region] ?? 0;
                $maxValue = max($maxValue, $value);
            }
        }
        return [
            'datasets' => $datasets,
            'labels' => $labels,
            'maxValue' => $maxValue,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected ?array $options = [
        'plugins' => [
            'legend' => [
                'display' => false,
            ],
        ],
    ];

    protected function getOptions(): array
    {   
        $data = $this->getData();
        $maxValue = $data['maxValue'] ?? 15;

        // Round up to nearest multiple of 5
        $roundedMax = ceil($maxValue / 5) * 5;
        return [
            'plugins' => [
                'legend' => [
                    'display' => true,
                ],
                'datalabels' => [
                    'anchor' => 'end',
                    'align' => 'end',
                    'color' => '#000',
                    'font' => [
                        'weight' => 'bold',
                    ],
                ],
            ],
            'scales' => [ 
                'x' => [
                    'stacked' => true,
                ],
                'y' => [
                    'stacked' => true,
                    'beginAtZero' => true,
                    'ticks' => [
                        'stepSize' => 5,
                        'min' => 0,
                        'max' => $roundedMax
                    ],
                ],
            ],
            'responsive' => true,
            'maintainAspectRatio' => false,
        ];
    }

    private function getDefaultFromDate(): string
    {
        $month = date('m');
        if ($month >= 4) {
            $y = date('Y');
        } else {
            $y = date('Y', strtotime('-1 year'));
        }
        return $y . "-04-01";
    }

    private function getDefaultToDate(): string
    {
        $month = date('m');
        if ($month >= 4) {
            $pt = date('Y', strtotime('+1 year'));
        } else {
            $pt = date('Y');
        }
        return $pt . "-03-31";
    }

    function find_region($state)
    {
        $customer_region = '';
        $indian_all_states  = \Config('oly.indian_all_states');

        if (in_array($state, $indian_all_states['north'])) {
            $customer_region =  "north";
        } elseif (in_array($state, $indian_all_states['east'])) {
            $customer_region = "east";
        } elseif (in_array($state, $indian_all_states['south'])) {
            $customer_region =  "south";
        } elseif (in_array($state, $indian_all_states['west'])) {
            $customer_region = "west";
        }
        return $customer_region;
    }
}


