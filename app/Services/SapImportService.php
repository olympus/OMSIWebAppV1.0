<?php

namespace App\Services;

use App\ServiceRequests;
use App\DirectRequest;
use App\Models\ProductInfo;
use App\AutoEmails;
use App\NotifyCustomer;
use Illuminate\Support\Facades\Mail;
use App\Mail\RequestUpdated;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Auth;

class SapImportService
{
    public function csv2array(string $filepath): array
    {
        ini_set('auto_detect_line_endings', true);
        $csv_data = [];
        if (($handle = fopen($filepath, "r")) !== false) {
            $row = 1;
            while (($data = fgetcsv($handle, 1000, "\t")) !== false) {
                if ($row > 1 && !empty($data)) {
                    $request_ids = explode(",", $data[0]);
                    foreach ($request_ids as $request_id) {
                        if (strtolower($request_id) !== "ssbd") {
                            $csv_data[] = [
                                'cvm_req_complete' => $data[0],
                                'cvm_req_no' => ltrim(trim($request_id), '0'),
                                'sap_id' => ltrim(trim($data[1]), '0'),
                                'status' => $data[2],
                                'fse_code' => $data[3],
                                'prod_model_no' => $data[4],
                                'customer_code' => $data[5],
                                'customer_name' => $data[6],
                                'customer_city' => $data[7],
                                'customer_state' => $data[8],
                                'prod_material' => $data[9],
                                'prod_serial_no' => $data[10],
                                'prod_equipment_no' => ltrim(trim($data[11]), '0'),
                                'prod_material_description' => $data[12],
                            ];
                        }
                    }
                }
                $row++;
            }
            fclose($handle);
        }
        return $csv_data;
    }

    public function validate_status(string $status): string
    {
        $map = [
            'Received' => ['received'],
            'Assigned' => ['assigned', 'troubleshoot', 'others', 'onsite repaired'],
            'Attended' => ['attended'],
            'Received_At_Repair_Center' => ['received_at_repair_center', 'received at repair center', 'brought to sc'],
            'Quotation_Prepared' => ['quotation_prepared', 'quotation prepared'],
            'PO_Received' => ['po_received', 'po received'],
            'Repair_Started' => ['repair_started', 'repair started'],
            'Repair_Completed' => ['repair_completed', 'repair completed'],
            'Ready_To_Dispatch' => ['ready_to_dispatch', 'ready to dispatch'],
            'Dispatched' => ['dispatched'],
            'Closed' => ['closed'],
        ];

        foreach ($map as $key => $values) {
            if (in_array(strtolower($status), $values)) {
                return $key;
            }
        }

        return $status;
    }

    public function validate_status_increment(string $previous, string $new): bool
    {
        $status_keys = Config::get('oly.requests_statuses');
        foreach ($status_keys as $group) {
            $statuses = array_keys($group);
            if (in_array($previous, $statuses) && in_array($new, $statuses)) {
                return array_search($previous, $statuses) <= array_search($new, $statuses);
            }
        }
        return true;
    }

    public function checkStatus(string $existing, string $new): array
    {
        $allowed = ['Received', 'Assigned', 'Attended', 'Received_At_Repair_Center', 'Quotation_Prepared', 'PO_Received', 'Repair_Started', 'Repair_Completed', 'Ready_To_Dispatch', 'Dispatched', 'Closed'];

        if (!in_array($new, $allowed)) {
            return ['table_action' => 'Unknown Status', 'checked' => 0];
        }

        if ($new === $existing) {
            return ['table_action' => 'Status Unchanged', 'checked' => 0];
        }

        $increment = $this->validate_status_increment($existing, $new);
        return [
            'table_action' => $increment ? 'Status Update' : 'Status Rollback',
            'checked' => $increment ? 1 : 0,
        ];
    }

    public function makeRowDataArr(array $data, array $temp_row, string $requests, string $request_id, string $new_status): array
    {
        $type = strpos(strtolower($temp_row['action']), 'direct') !== false ? 'direct' : 'cvm';
        $previous_status = $temp_row['previous_status'] === 'BROUGHT TO SC' ? 'Received_At_Repair_Center' : $temp_row['previous_status'];

        return array_merge($data, [
            'type' => $type,
            'orig_cvm_col' => $requests,
            'cvm_req_no' => $temp_row['cvm_req_no'],
            'request_id' => $request_id,
            'previous_status' => $previous_status,
            'status' => $new_status,
            'action' => $temp_row['action'],
            'checked' => $temp_row['checked'],
            'trigger' => $temp_row['trigger'],
        ]);
    }

    public function checkProductExists(string $type, array $data): void
    {
        $request_id = $type === 'sap'
            ? ServiceRequests::where('sap_id', $data['sap_id'])->value('id')
            : $data['cvm_req_no'];

        if (!$request_id) {
            $request_id = $data['cvm_req_no'];
        }

        $product_data = [
            'service_requests_id' => $request_id,
            'pd_name' => $data['prod_model_no'],
            'pd_serial' => $data['prod_serial_no'],
            'pd_description' => $data['prod_material_description'],
        ];

        $product = ProductInfo::where('service_requests_id', $request_id)->first();
        if (!$product) {
            ProductInfo::create($product_data);
        }
    }

    public function process_store(array $data_all): array
    {
        $messages = [];
        $counter = 1;

        foreach ($data_all as $data) {
            switch ($data['trigger']) {
                case 'cvm_update_sapid':
                case 'cvm_update_cvmid':
                case 'cvm_split_request':
                    $messages[] = [$counter, "CVM logic not yet implemented", $data['request_id'], $data['type'], null];
                    break;

                case 'direct_new':
                    $id = DirectRequest::create($data)->id;
                    $messages[] = [$counter, "Direct Request with $id created.", $id, $data['type']];
                    break;

                case 'direct_update':
                    $request = DirectRequest::where('sap_id', $data['sap_id'])->first();
                    if ($request) {
                        $request->update($data);
                        $messages[] = [$counter, "Direct Request with {$request->id} updated.", $request->id, $data['type']];
                    }
                    break;
            }

            $counter++;
        }

        return $messages;
    }
}
