<?php

namespace App\Exports;

use App\Models\Departments;
use App\Models\Hospitals;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Carbon\Carbon;

class CustomerDataExport implements FromCollection, WithHeadings
{
    protected Collection $data;

    public function __construct(Collection $data)
    {
        $this->data = $data;
    }

    private function formatDate($date)
    {
        return !empty($date)
            ? \Carbon\Carbon::parse($date)->format('j M Y h:i a')
            : '';
    }


    public function collection(): Collection
    {
        return $this->data->map(function ($item) {

            // Get hospitals
            $hospitals = Hospitals::where('customer_id', $item->id)->get();

            $hospital_names = $hospitals
                ->pluck('hospital_name')
                ->filter()
                ->implode(', ');

            $city = [];
            $state = [];
            $region = [];
            $branch = [];
            $allDeptIds = [];

            foreach ($hospitals as $hospital) {

                // Collect department IDs
                if (!empty($hospital->dept_id)) {
                    $dept_ids = array_filter(explode(',', $hospital->dept_id));
                    $allDeptIds = array_merge($allDeptIds, $dept_ids);
                }

                // Region safe handling
                $stateName = $hospital->state ?? '';
                $regionName = function_exists('find_region')
                    ? find_region($stateName)
                    : $stateName;

                $region[] = $regionName ? ucfirst($regionName) : '';

                $city[] = $hospital->city ?? '';
                $state[] = $stateName;
                $branch[] = $hospital->responsible_branch ?? '';
            }

            // Fetch departments safely
            $depart_names = '';

            $uniqueDeptIds = array_unique(array_filter($allDeptIds));

            if (!empty($uniqueDeptIds)) {
                $departments = Departments::whereIn('id', $uniqueDeptIds)
                    ->pluck('name')
                    ->filter()
                    ->all();

                $depart_names = implode(', ', $departments);
            }

            return [
                'ID' => $item->id ?? '',
                'SAP Customer ID' => $item->sap_customer_id ?? '',
                'Customer ID' => $item->customer_id ?? '',
                'Title' => $item->title ?? '',
                'Customer Type' => $item->customer_type ?? '',
                'First Name' => $item->first_name ?? '',
                'Last Name' => $item->last_name ?? '',
                'Mobile Number' => $item->mobile_number ?? '',
                'Email' => $item->email ?? '',
                'Mobile OTP Code' => $item->mobile_otp ?? '',
                'Email OTP Code' => $item->email_otp ?? '',
                'Valid Up To' => $item->valid_upto ?? '',
                'Hospital ID' => $item->hospital_id ?? '',
                'Platform' => $item->platform ?? '',
                'App Version' => $item->app_version ?? '',
                'Created At' => $this->formatDate($item->created_at),
                'Updated At' => $this->formatDate($item->updated_at),
                'City' => implode(',', array_unique(array_filter($city))),
                'State' => implode(',', array_unique(array_filter($state))),
                'Region' => implode(',', array_unique(array_filter($region))),
                'Branch' => implode(',', array_unique(array_filter($branch))),
                'Hospital Names' => $hospital_names,
                'Departments' => $depart_names,
                'Account Is Verified' => $item->is_verified == 1 ? 'Yes' : 'No',
                'Account Verified At' => $this->formatDate($item->account_verify_at),
                'Is Biometric Enable' => $item->is_face_id == 1 ? 'Yes' : 'No',
                'Is MPIN Enable' => $item->is_mpin == 1 ? 'Yes' : 'No',
                'Account Is Block' => $item->is_account_block == 1 ? 'Yes' : 'No',
                'Is Password Expired' => $item->is_expired == 1 ? 'Yes' : 'No',
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
            'Customer Type',
            'First Name',
            'Last Name',
            'Mobile Number',
            'Email',
            'Mobile OTP Code',
            'Email OTP Code',
            'Valid Up To',
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
            'Account Is Verified',
            'Account Verified At',
            'Is Biometric Enable',
            'Is MPIN Enable',
            'Account Is Block',
            'Is Password Expired',
        ];
    }
}
