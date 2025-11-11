<?php

namespace App\Filament\Resources\ServiceRequestData\Pages;

use App\Filament\Resources\ServiceRequestData\ServiceRequestDataResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Actions\Action;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\ServiceRequests;
use App\Models\ArchiveServiceRequests;
use App\Models\Customers;
use App\Models\Hospitals;
use App\Models\StatusTimeline;
use App\NotifyCustomer;
use App\Services\SFDC;
use App\Events\RequestStatusUpdated;
use Illuminate\Database\Eloquent\Model;
use Filament\Notifications\Notification;

class EditServiceRequestData extends EditRecord
{
    protected static string $resource = ServiceRequestDataResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Optional: modify form data before saving
        return $data;
    }

    /**
     * Override form actions in Filament 4.
     */
    protected function getFormActions(): array
    {
        return [
            Action::make('update_request')
                ->label('Update Request')
                ->submit('save'), // tells Filament to trigger form submission
        ];
    }

    /**
     * This method runs when the form is submitted.
     * Filament 4 now uses this method instead of action closures.
     */
    protected function handleRecordUpdate(Model $record, array $data): Model
    {   
        $oldData = clone $record; 

        $customer = Customers::findOrFail($record->customer_id);
        $fullData = array_merge($record->toArray(), $data);
        //dd($fullData);
        // --- Handle request_type change ---
        if ($oldData['request_type'] !== $fullData['request_type']) {
            if (env("SFDC_ENABLED") && $fullData['request_type'] === 'service' && !$oldData['sfdc_id']) {
                $hospital = Hospitals::find($record->hospital_id);

                $sfdcResponse = SFDC::createRequest($record, $customer, $hospital, "");
                if (
                    !empty($sfdcResponse->success)
                    && $sfdcResponse->success === "true"
                    && isset($sfdcResponse->id)
                ) {
                    $record->sfdc_id = $sfdcResponse->id;
                    $record->save();
                } else {
                    Log::info("\n===Error SFDCCreateRequest request_type_change===\n", (array) $sfdcResponse);
                }
            }

            $record->request_type = $fullData['request_type'];
            $record->sub_type = $fullData['sub_type'] ?? null;
            $record->save();

            NotifyCustomer::send_notification('request_type_changed', $record, $customer);
            $this->notify('success', 'Request Type successfully changed.');
            //return;
        }

        // --- Update other fields ---
       
        /*$req = ServiceRequests::where('id', $record['id'])->first();
        if($req){
            ServiceRequests::where('id', $record['id'])->update([
                'customer_id' => $fullData['customer_id'],
                'hospital_id' => $fullData['hospital_id'],
                'dept_id' => $fullData['dept_id'],
                'status' => $fullData['status'],
                'sap_id' => $fullData['sap_id'] ?? null,
                'sfdc_id' => $fullData['sfdc_id'] ?? null,
                'sfdc_customer_id' => $fullData['sfdc_customer_id'] ?? null,
                'employee_code' => $fullData['employee_code'] ?? null,
                'last_updated_by' => Auth::user()->name,
            ]);
        }else{
            $req = ArchiveServiceRequests::where('id', $record['id'])->first(); 
            ArchiveServiceRequests::where('id', $record['id'])->update([
                'customer_id' => $fullData['customer_id'],
                'hospital_id' => $fullData['hospital_id'],
                'dept_id' => $fullData['dept_id'],
                'status' => $fullData['status'],
                'sap_id' => $fullData['sap_id'] ?? null,
                'sfdc_id' => $fullData['sfdc_id'] ?? null,
                'sfdc_customer_id' => $fullData['sfdc_customer_id'] ?? null,
                'employee_code' => $fullData['employee_code'] ?? null,
                'last_updated_by' => Auth::user()->name,
            ]);
        }*/

        // Try to find record in ServiceRequests first
        
        $req = ServiceRequests::find($record['id']);

        if (!$req) {
            // If not found, check ArchiveServiceRequests
            $req = ArchiveServiceRequests::find($record['id']);
        }

        // If record is not found in both tables
        if (!$req) {
            throw new \Exception("Service Request record with ID {$record['id']} not found in either table.");
        }

        // Determine which model to update
        $model = $req instanceof ServiceRequests ? ServiceRequests::class : ArchiveServiceRequests::class;

        // Prepare update data
        $updateData = [
            'customer_id' => $fullData['customer_id'] ?? null,
            'hospital_id' => $fullData['hospital_id'] ?? null,
            'dept_id' => $fullData['dept_id'] ?? null,
            'status' => $fullData['status'] ?? null,
            'sap_id' => $fullData['sap_id'] ?? null,
            'sfdc_id' => $fullData['sfdc_id'] ?? null,
            'sfdc_customer_id' => $fullData['sfdc_customer_id'] ?? null,
            'employee_code' => $fullData['employee_code'] ?? null,
            'last_updated_by' => Auth::user()->name ?? 'System',
        ];

        // Perform update
        $model::where('id', $record['id'])->update($updateData);

        // Optional: return updated record
        $updatedRecord = $model::find($record['id']);
 

        // --- Add status timeline ---
        StatusTimeline::create([
            'status' => $record->status,
            'customer_id' => $record->customer_id,
            'request_id' => $record->id,
        ]);

        // --- Notify and fire events ---
        NotifyCustomer::send_notification('request_update', $record, $customer);
        event(new RequestStatusUpdated($record, $customer, $oldData));
        // ✅ Show update success message
        // Notification::make()
        //     ->title('Request data successfully updated')
        //     ->success()
        //     ->send(); 
        return $record;

    }
}
