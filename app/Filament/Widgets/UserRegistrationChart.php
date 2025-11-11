<?php

namespace App\Filament\Widgets;

use App\Models\ServiceRequests;
use App\Models\Customers;
use App\Models\Hospitals;
use App\Models\ArchiveServiceRequests;
use Filament\Widgets\ChartWidget;
use Filament\Forms\Components\DatePicker;
use Filament\Schemas\Schema;
use Filament\Forms\Concerns\InteractsWithForms;
use Illuminate\Support\Carbon;
use Filament\Widgets\ChartWidget\Concerns\HasFiltersSchema;

class UserRegistrationChart extends ChartWidget
{
    use HasFiltersSchema, InteractsWithForms;

    protected ?string $heading = '2. User registration trend (Geographic)';

    protected int | string | array $columnSpan = 'full';

    protected ?string $maxHeight = '800px';

    protected bool $isCollapsible = true;

    public function filtersSchema(Schema $schema): Schema
    {
        return $schema->components([
            DatePicker::make('startDate'),
            DatePicker::make('endDate'),
        ]);
    }

    protected function getData(): array
    {
        $formData = $this->form->getState();
        $fromDate = $this->filters['startDate'] ?? $this->getDefaultFromDate();
        $toDate = $this->filters['endDate'] ?? $this->getDefaultToDate();

        $currentRequests = Customers::selectRaw('DATE(created_at) as date, customers.*')->whereBetween('created_at', [$fromDate, $toDate])->where('email', 'NOT LIKE', '%olympus-ap.com%')->get();   
        $monthlyRegionData = []; 

        // Process current requests
        foreach ($currentRequests as $request) {
            foreach (explode(",", $request->hospital_id) as $key) {
                $hospital_state = Hospitals::where('id', $key)->value('state');
                $month = Carbon::parse($request->date)->format('M Y');
                $service_region = $this->find_region($hospital_state ?? '');
                $region = $service_region ?: 'Unknown';

                if (!isset($monthlyRegionData[$month])) {
                    $monthlyRegionData[$month] = [];
                }
                if (!isset($monthlyRegionData[$month][$region])) {
                    $monthlyRegionData[$month][$region] = 0;
                }
                $monthlyRegionData[$month][$region]++;
            }
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


