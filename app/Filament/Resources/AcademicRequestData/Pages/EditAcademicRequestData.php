<?php

namespace App\Filament\Resources\AcademicRequestData\Pages;

use App\Filament\Resources\AcademicRequestData\AcademicRequestDataResource;
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

class EditAcademicRequestData extends EditRecord
{
    protected static string $resource = AcademicRequestDataResource::class;

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

        // --- Handle request_type change ---
        if ($oldData['request_type'] !== $fullData['request_type']) {
             
            $record->request_type = $fullData['request_type'];
            $record->sub_type = $fullData['sub_type'] ?? null;
            $record->save();

            NotifyCustomer::send_notification('request_type_changed', $record, $customer);
            $this->notify('success', 'Request Type successfully changed.');
            //return;
        }

        // --- Update other fields ---
        
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
        
        return $record;

    }
}
