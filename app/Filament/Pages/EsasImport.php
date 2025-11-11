<?php

namespace App\Filament\Pages;

use App\Models\ServiceRequests;
use Filament\Pages\Page;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\EsasController;
use Excel;
use Exception;

class EsasImport extends Page
{
    use WithFileUploads;

    public $attachment;
    public array $tableData = [];
    public array $selectedRows = [];

    protected static ?string $navigationLabel = 'ESAS Import';
    protected static ?string $slug = 'esas-import';
    protected static ?string $title = 'ESAS Import';
    protected static string|null|\BackedEnum $navigationIcon = 'heroicon-o-arrow-down-tray';
    protected static ?int $navigationSort = 3;
    protected static ?int $navigationGroupSort = 5;
    protected string $view = 'filament.pages.esas-import';

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
            if (!in_array($extension, ['xlsx', 'xls', 'csv', 'txt'])) {
                $this->addError('attachment', 'Please select a valid Excel or CSV file.');
                return;
            }

            $filename = 'ESASImport_' . time() . '.' . $extension;
            $path = $this->attachment->storeAs('exports', $filename);

            $controller = new EsasController();
            $filepath = Storage::path($path);

            // Check if file exists and is readable
            if (!file_exists($filepath) || !is_readable($filepath)) {
                $this->addError('attachment', 'Unable to read the uploaded file.');
                return;
            }

            // Process Excel file using ESAS controller logic
            $csv_data = [];
            Excel::selectSheets('Sheet1')->load($filepath, function($reader) use (&$csv_data) {
                $results = $reader->toArray();
                foreach($results as $result){
                    $myvoiceno = strtolower($result['my_voice_no.']);
                    if(strpos($myvoiceno, ".") !== false){
                        $myvoiceno1 = ServiceRequests::where("import_id", $myvoiceno)->value('id');
                        $myvoiceno = (is_null($myvoiceno1)) ? $myvoiceno : $myvoiceno1 ;
                    }
                    if(!empty($myvoiceno) && is_numeric($myvoiceno)){
                        $csv_data[] = [
                            'external_no.' => $result['external_no.'],
                            'status' => $result['repair_status'],
                            'cvm_req_no' => $myvoiceno,
                        ];
                    }
                }
            });

            // Check if data is empty
            if (empty($csv_data)) {
                $this->addError('attachment', 'The uploaded file appears to be empty or has no valid data.');
                return;
            }

            $classified = [];
            $processedCount = 0;

            foreach ($csv_data as $data) {
                try {
                    $request_id = $data['cvm_req_no'];
                    $new_status = $controller->validate_status(strtolower($data['status']));

                    // Determine action type based on ESAS controller logic
                    $existingCVM = ServiceRequests::where("id", $request_id)->first();
                    if (!is_null($existingCVM)) {
                        $action_type = "CVM Existing";
                    } else {
                        $action_type = "CVM Deleted";
                    }

                    // Status check with ESAS-specific validation
                    $previous_status = $existingCVM->status ?? 'Received';
                    $checkStatus = $controller->checkStatus($previous_status, $new_status);

                    // Prepare temp row
                    $tempRow = [
                        'cvm_req_no' => $request_id,
                        'previous_status' => $previous_status,
                        'action' => $checkStatus['table_action'],
                        'checked' => $checkStatus['checked'],
                        'trigger' => match ($action_type) {
                            'CVM Existing' => 'cvm_update_cvmid',
                            default => 'cvm_ignore',
                        },
                    ];

                    $rowData = $controller->makeRowDataArr($tempRow, $new_status);
                    $classified[] = $rowData;

                    $processedCount++;
                } catch (\Exception $e) {
                    // Log individual row errors but continue processing
                    Log::warning('ESAS Import row processing error: ' . $e->getMessage(), ['data' => $data]);
                }
            }

            $this->tableData = $classified;

            // Add success message with processing summary
            $this->addSuccessMessage("File processed successfully. {$processedCount} records ready for import.");

        } catch (\Exception $e) {
            $this->addErrorMessage('ESAS Import processing failed: ' . $e->getMessage());

            // Send fallback error email like original controller
            Mail::raw('ESAS Bulk Import Error in Filament: <br><br>'. $e->getMessage() . '<br><br>File: ' . ($filepath ?? 'Unknown'), function($message){
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

        $controller = new EsasController();
        $selected = array_filter($this->tableData, fn($row) => in_array($row['cvm_req_no'], $this->selectedRows));

        // Validate that selected rows exist in table data
        if (empty($selected)) {
            $this->addErrorMessage('Selected rows not found in the data. Please try again.');
            return;
        }

        try {
            $messages = $controller->process_store(array_values($selected));

            // Log successful import
            Log::info('ESAS Import completed successfully', [
                'processed_rows' => count($selected),
                'messages_count' => count($messages),
                'user' => Auth::user()->name ?? 'system'
            ]);

            session()->flash('messages', $messages);

            // Add success notification with details
            $this->addSuccessMessage('ESAS Import completed successfully. ' . count($selected) . ' records processed.');

        } catch (\Exception $e) {
            // Log the error
            Log::error('ESAS Import failed', [
                'error' => $e->getMessage(),
                'selected_rows' => count($selected),
                'user' => Auth::user()->name ?? 'system',
                'trace' => $e->getTraceAsString()
            ]);

            // Add error notification with fallback email like original controller
            $this->addErrorMessage('ESAS Import failed: ' . $e->getMessage());

            // Send fallback error email like original controller does
            Mail::raw('ESAS Bulk Import Error in Filament: <br><br>'. $e->getMessage() . '<br><br>Selected Rows: ' . count($selected), function($message){
                $message->from('no-reply@olympusmyvoice.com', 'Olympus My Voice App');
                $message->to('sarvar.kumar+alert@weareflamingo.in');
            });
        }

        $this->tableData = [];
        $this->selectedRows = [];
    }

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
