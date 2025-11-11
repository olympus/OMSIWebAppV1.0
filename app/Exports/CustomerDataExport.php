<?php

namespace App\Exports;

use App\Models\Departments;
use App\Models\Hospitals;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class CustomerDataExport implements FromCollection, WithHeadings
{
    protected $data;

    public function __construct(Collection $data)
    {
        $this->data = $data;
    }

    public function collection(): Collection
    {
        return $this->data->map(function ($item) {
            // Get hospitals for this customer
            $hospitals = Hospitals::where('customer_id', $item->id)->get();
            $hospitals_name = Hospitals::where('customer_id', $item->id)->pluck('hospital_name')->all();
            $hospital_names = implode(', ', $hospitals_name);
            
            $city = [];
            $state = [];
            $region = [];
            $branch = [];
            $depart_names = '';
            
            foreach ($hospitals as $hospital) {
                if (!empty($hospital->dept_id)) {
                    $dept_ids = explode(',', $hospital->dept_id);
                    $departments = Departments::whereIn('id', $dept_ids)->pluck('name')->all();
                    $depart_names = implode(', ', $departments);
                }
                
                // Find region using helper function if available
                $region[] = ucfirst(function_exists('find_region') ? find_region($hospital->state) : ($hospital->state ?? ''));
                $city[] = $hospital->city ?? '';
                $state[] = $hospital->state ?? '';
                $branch[] = $hospital->responsible_branch ?? '';
            }

            return [
                'ID' => $item->id ?? '',
                'SAP Customer ID' => $item->sap_customer_id ?? '',
                'Customer ID' => $item->customer_id ?? '',
                'Title' => $item->title ?? '',
                'First Name' => $item->first_name ?? '',
                'Last Name' => $item->last_name ?? '',
                'Mobile Number' => $item->mobile_number ?? '',
                'Email' => $item->email ?? '',
                'Is Verified' => $item->is_verified ?? '',
                'OTP Code' => $item->otp_code ?? '',
                'Hospital ID' => $item->hospital_id ?? '',
                'Platform' => $item->platform ?? '',
                'App Version' => $item->app_version ?? '',
                'Created At' => $item->created_at ? $item->created_at->format('j M Y h:i a') : '',
                'Updated At' => $item->updated_at ? $item->updated_at->format('j M Y h:i a') : '',
                'City' => implode(',', array_unique($city)),
                'State' => implode(',', array_unique($state)),
                'Region' => implode(',', array_unique($region)),
                'Branch' => implode(',', array_unique($branch)),
                'Hospital Names' => $hospital_names,
                'Departments' => $depart_names,
            ];
        });
    }

    public function headings(): array
    {
        return [
            'ID',
            'SAP Customer ID',
            'Customer ID',
            'Title',
            'First Name',
            'Last Name',
            'Mobile Number',
            'Email',
            'Is Verified',
            'OTP Code',
            'Hospital ID',
            'Platform',
            'App Version',
            'Created At',
            'Updated At',
            'City',
            'State',
            'Region',
            'Branch',
            'Hospital Names',
            'Departments',
        ];
    }
}