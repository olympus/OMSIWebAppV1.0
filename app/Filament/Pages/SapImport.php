<?php

namespace App\Filament\Pages;

use App\Models\DirectRequest;
use App\Models\ServiceRequests;
use Filament\Pages\Page;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\SapController;
use Exception;

class SapImport extends Page
{
    use WithFileUploads;

    public $attachment;
    public array $tableData = [];
    public array $selectedRows = [];

    // Add this method to handle Livewire file upload hydration
    public function updatedAttachment()
    {
        // This method is called when the attachment property is updated
        // It helps Livewire properly handle file uploads
        $this->validate([
            'attachment' => 'required',
        ]);
    }

    // Add a method to check if file is ready
    public function hasValidFile()
    {
        return $this->attachment && is_object($this->attachment);
    }

    protected static ?string $navigationLabel = 'SAP Import';
    protected static ?string $slug = 'sap-import';
    protected static ?string $title = 'SAP Import';
    protected static string|null|\BackedEnum $navigationIcon = 'heroicon-o-arrow-down-tray';
    protected static ?int $navigationSort = 2;
    protected static ?int $navigationGroupSort = 5;
    protected string $view = 'filament.pages.sap-import';

    public function submit()
    {
        try {
            // Check if file is uploaded (handle Livewire file upload properly)
            if (!$this->attachment) {
                $this->addError('attachment', 'Please select a file.');
                return;
            }

            // In Livewire, the file should be available as a temporary uploaded file
            if (!is_object($this->attachment) || !method_exists($this->attachment, 'getClientOriginalExtension')) {
                $this->addError('attachment', 'File not properly uploaded. Please try again.');
                return;
            }

            // Validate file type
            $extension = strtolower($this->attachment->getClientOriginalExtension());
            if (!in_array($extension, ['csv', 'txt', 'xlsx'])) {
                $this->addError('attachment', 'Please select a valid CSV, TXT, or XLSX file. Got: ' . $extension);
                return;
            }

            $filename = 'SAPImport_' . time() . '.' . $extension;
            $path = $this->attachment->storeAs('exports', $filename);

            $controller = new SapController();
            $filepath = Storage::path($path);

            // Check if file exists and is readable
            if (!file_exists($filepath) || !is_readable($filepath)) {
                $this->addError('attachment', 'Unable to read the uploaded file.');
                return;
            }

            // Add proper encoding handling like original controller
            $rawCsvData = $controller->csv2array($filepath);
            $csvData = $controller->ascii2utf8($rawCsvData);

            // Check if CSV data is empty
            if (empty($csvData)) {
                $this->addError('attachment', 'The uploaded file appears to be empty or has no valid data.');
                return;
            }

            // Validate Excel columns like original controller
            try {
                $controller->verify_excel(array_keys($csvData[0]));
            } catch (\Exception $e) {
                $this->addError('attachment', 'Excel file format is invalid: ' . $e->getMessage());
                return;
            }

            $classified = [];
            $processedCount = 0;

            foreach ($csvData as $data) {
                try {
                    $request_id = $data['cvm_req_no'];
                    $new_status = $controller->validate_status(strtolower($data['status']));

                    // Determine action type based on original controller logic
                    $action_type = 'CVM Deleted';
                    $existingSAP = ServiceRequests::where('sap_id', $data['sap_id'])->first();
                    $existingCVM = ServiceRequests::where('id', $request_id)->first();

                    if (str_contains(strtolower($request_id), 'direct') || str_contains(strtolower($request_id), 'n/a')) {
                        $is_direct_new = DirectRequest::where('sap_id', $data['sap_id'])->first();
                        $action_type = is_null($is_direct_new) ? 'Direct New' : 'Direct Existing';
                    } elseif (!is_null($existingSAP)) {
                        $action_type = !is_null($existingCVM) ? 'SAP Existing' : 'CVM Deleted';
                    } elseif (!is_null($existingCVM)) {
                        $action_type = 'CVM Existing';
                    }

                    if (str_contains($request_id, '.')) {
                        $action_type = 'CVM Split Request';
                    }

                    // Status check with proper validation
                    $previous_status = $existingCVM->status ?? 'Received';
                    $checkStatus = $controller->checkStatus($previous_status, $new_status);

                    // Prepare temp row
                    $tempRow = [
                        'cvm_req_no' => $request_id,
                        'previous_status' => $previous_status,
                        'action' => $action_type === 'Direct New' || $action_type === 'Direct Existing'
                            ? $action_type
                            : $checkStatus['table_action'],
                        'checked' => $action_type === 'Direct New' || $action_type === 'Direct Existing'
                            ? 1
                            : $checkStatus['checked'],
                        'trigger' => match ($action_type) {
                            'Direct New' => 'direct_new',
                            'Direct Existing' => 'direct_update',
                            'SAP Existing' => 'cvm_update_sapid',
                            'CVM Existing' => 'cvm_update_cvmid',
                            'CVM Split Request' => 'cvm_split_request',
                            default => 'cvm_ignore',
                        },
                    ];

                    $rowData = $controller->makeRowDataArr($data, $tempRow, $data['cvm_req_complete'], $request_id, $new_status);
                    $classified[] = $rowData;

                    // Add missing product existence check like original controller
                    $filter_data_type = (strpos(strtolower($tempRow['action']), 'direct') !== false) ? 'direct' : 'cvm';
                    $controller->checkProductExists($filter_data_type, $data);

                    $processedCount++;
                } catch (\Exception $e) {
                    // Log individual row errors but continue processing
                    \Log::warning('SAP Import row processing error: ' . $e->getMessage(), ['data' => $data]);
                }
            }

            $this->tableData = $classified;

            // Add success message with processing summary
            $this->addSuccessMessage("File processed successfully. {$processedCount} records ready for import.");

        } catch (\Exception $e) {
            $this->addErrorMessage('SAP Import processing failed: ' . $e->getMessage());

            // Send fallback error email like original controller
            Mail::raw('SAP Bulk Import Error in Filament: <br><br>'. $e->getMessage() . '<br><br>File: ' . ($filepath ?? 'Unknown'), function($message){
                $message->from('no-reply@olympusmyvoice.com', 'Olympus My Voice App');
                $message->to('sarvar.kumar+alert@weareflamingo.in');
            });
        }
    }

    public function finalizeImport()
    {
        // Validate that rows are selected
        if (empty($this->selectedRows)) {
            $this->addErrorMessage('Please select at least one row to import.');
            return;
        }

        // Validate that table data exists
        if (empty($this->tableData)) {
            $this->addErrorMessage('No data available for import. Please upload a file first.');
            return;
        }

        $controller = new SapController();
        $selected = array_filter($this->tableData, fn($row) => in_array($row['cvm_req_no'], $this->selectedRows));

        // Validate that selected rows exist in table data
        if (empty($selected)) {
            $this->addErrorMessage('Selected rows not found in the data. Please try again.');
            return;
        }

        try {
            $messages = $controller->process_store(array_values($selected));

            // Log successful import
            Log::info('SAP Import completed successfully', [
                'processed_rows' => count($selected),
                'messages_count' => count($messages),
                'user' => Auth::user()->name ?? 'system'
            ]);

            session()->flash('messages', $messages);

            // Add success notification with details
            $this->addSuccessMessage('SAP Import completed successfully. ' . count($selected) . ' records processed.');

        } catch (\Exception $e) {
            // Log the error
            Log::error('SAP Import failed', [
                'error' => $e->getMessage(),
                'selected_rows' => count($selected),
                'user' => Auth::user()->name ?? 'system',
                'trace' => $e->getTraceAsString()
            ]);

            // Add error notification with fallback email like original controller
            $this->addErrorMessage('SAP Import failed: ' . $e->getMessage());

            // Send fallback error email like original controller does
            Mail::raw('SAP Bulk Import Error in Filament: <br><br>'. $e->getMessage() . '<br><br>Selected Rows: ' . count($selected), function($message){
                $message->from('no-reply@olympusmyvoice.com', 'Olympus My Voice App');
                $message->to('sarvar.kumar+alert@weareflamingo.in');
            });
        }

        $this->tableData = [];
        $this->selectedRows = [];
    }

    private function addSuccessMessage($message)
    {
        // Filament notification - you can customize this based on your Filament setup
        $this->dispatch('notify', [
            'type' => 'success',
            'message' => $message
        ]);
    }

    private function addErrorMessage($message)
    {
        // Filament notification - you can customize this based on your Filament setup
        $this->dispatch('notify', [
            'type' => 'error',
            'message' => $message
        ]);
    }
}
