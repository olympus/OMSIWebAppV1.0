<?php

namespace App\Filament\Pages;

use App\Models\ArchiveServiceRequests;
use App\Models\ServiceRequests;
use App\Models\Customers;
use App\DownloadExcelMail;
use App\Mail\ArchiveRequestDataMail;
use App\Mail\ArchiveCustomerDataMail;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms;
use Filament\Pages\Page;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Mail;
use Maatwebsite\Excel\Facades\Excel;

class ArchiveDataFilter extends Page implements Forms\Contracts\HasForms, HasActions
{
    use Forms\Concerns\InteractsWithForms;
    use InteractsWithActions;
    protected static ?string $title = 'Archive Data Filter';
    protected static ?string $navigationLabel = 'Archive Requests';
    protected static string|null|\BackedEnum $navigationIcon = 'heroicon-o-funnel';
    protected static ?int $navigationSort = 4;
    protected static ?int $navigationGroupSort = 5;

    protected string $view = 'filament.pages.archive-data-filter';

    // Form data properties
    public $from_date;
    public $to_date;
    public $request_type = 'all';
    public $sub_type;
    public $status;
    public $email;
    public $cc_email;

    public function mount(): void
    {
        $this->request_type = 'all';
    }

    public function send_request_dataAction(): Action
    {
        return Action::make('send_request_data')
            ->label('Send Request Data')
            ->color('primary')
            ->requiresConfirmation()
            ->modalHeading('Send Request Data')
            ->modalDescription('Are you sure you want to send the request data via email?')
            ->modalSubmitActionLabel('Send')
            ->action(fn () => $this->handleSendRequestData());
    }

    public function download_request_dataAction(): Action
    {
        return Action::make('download_request_data')
            ->label('Download Request Data')
            ->color('success')
            ->requiresConfirmation()
            ->modalHeading('Download Request Data')
            ->modalDescription('Are you sure you want to download the request data?')
            ->modalSubmitActionLabel('Download')
            ->action(fn () => $this->handleDownloadRequestData());
    }

    public function send_customer_dataAction(): Action
    {
        return Action::make('send_customer_data')
            ->label('Send Customer Data')
            ->color('warning')
            ->requiresConfirmation()
            ->modalHeading('Send Customer Data')
            ->modalDescription('Are you sure you want to send the customer data via email?')
            ->modalSubmitActionLabel('Send')
            ->action(fn () => $this->handleSendCustomerData());
    }

    protected function getHeaderActions(): array
    {
        return [];
    }

    // Handler Methods
    public function handleSendRequestData()
    {
        try {
            \Log::info('Send Request Data triggered');

            $data = [
                'from_date' => $this->from_date,
                'to_date' => $this->to_date,
                'request_type' => $this->request_type ?? 'all',
                'sub_type' => $this->sub_type,
                'status' => $this->status,
                'email' => $this->email,
                'cc_email' => $this->cc_email,
            ];
            \Log::info('Form data:', $data);

            // Validation
            if (empty($data['from_date']) || empty($data['to_date'])) {
                throw new \Exception('From date and To date are required.');
            }

            if (empty($data['email'])) {
                throw new \Exception('Email is required.');
            }

            \Log::info('Validation passed');
            $this->sendRequestData($data);
            \Log::info('Email sent successfully');
        } catch (\Exception $e) {
            \Log::error('Error in handleSendRequestData: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());

            Notification::make()
                ->title('Error')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function handleDownloadRequestData()
    {
        try {
            \Log::info('Download Request Data triggered');

            $data = [
                'from_date' => $this->from_date,
                'to_date' => $this->to_date,
                'request_type' => $this->request_type ?? 'all',
                'sub_type' => $this->sub_type,
                'status' => $this->status,
            ];
            \Log::info('Form data:', $data);

            if (empty($data['from_date']) || empty($data['to_date'])) {
                throw new \Exception('From date and To date are required.');
            }

            \Log::info('Validation passed');
            return $this->downloadRequestData($data);
        } catch (\Exception $e) {
            \Log::error('Error in handleDownloadRequestData: ' . $e->getMessage());

            Notification::make()
                ->title('Error')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function handleSendCustomerData()
    {
        try {
            \Log::info('Send Customer Data triggered');

            $data = [
                'from_date' => $this->from_date,
                'to_date' => $this->to_date,
                'cc_email' => $this->cc_email,
            ];
            \Log::info('Form data:', $data);

            if (empty($data['from_date']) || empty($data['to_date'])) {
                throw new \Exception('From date and To date are required.');
            }

            if (empty($data['cc_email'])) {
                throw new \Exception('CC Email is required.');
            }

            \Log::info('Validation passed');
            $this->sendCustomerData($data);
            \Log::info('Customer email sent successfully');
        } catch (\Exception $e) {
            \Log::error('Error in handleSendCustomerData: ' . $e->getMessage());

            Notification::make()
                ->title('Error')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    // Helper Methods
    public function getEmailOptions()
    {
        return DownloadExcelMail::where('status', 1)->pluck('email', 'email')->toArray();
    }

    public function getSubTypeOptions($requestType): array
    {
        return match ($requestType) {
            'service' => [
                'all' => 'All',
                'BreakDown Call' => 'BreakDown Call',
                'Service Support' => 'Service Support',
            ],
            'academic' => [
                'all' => 'All',
                'Conference' => 'Conference',
                'Training' => 'Training',
                'Clinical Info' => 'Clinical Info',
            ],
            'enquiry' => [
                'all' => 'All',
                'Product Info' => 'Product Info',
                'Demonstration' => 'Demonstration',
                'Quotations' => 'Quotations',
            ],
            default => [],
        };
    }

    public function getStatusOptions($requestType): array
    {
        return match ($requestType) {
            'service' => [
                'all' => 'All',
                'received' => 'Received',
                'assigned' => 'Assigned',
                'attended' => 'Attended',
                'rarp' => 'Received At Repair Center',
                'qp' => 'Quotation Prepared',
                'por' => 'PO Received',
                'rs' => 'Repair Started',
                'rc' => 'Repair Completed',
                'rtd' => 'Ready To Dispatch',
                'dispatched' => 'Dispatched',
                'sc' => 'Under-Repair',
                'closed' => 'Closed',
            ],
            'academic', 'enquiry' => [
                'all' => 'All',
                'received' => 'Received',
                'assigned' => 'Assigned',
                'attended' => 'Attended',
                'closed' => 'Closed',
            ],
            default => [],
        };
    }
    // Action Methods
    private function sendRequestData(array $data): void
    {
        try {
            \Log::info('Starting sendRequestData');
            ini_set('max_execution_time', -1);
            ini_set('memory_limit', -1);
            
            $fromDate = Carbon::parse($data['from_date'])->startOfDay();
            $toDate = Carbon::parse($data['to_date'])->endOfDay();
            \Log::info("Date range: {$fromDate} to {$toDate}");
            
            $requestType = $data['request_type'] ?? 'all';
            $subType = $data['sub_type'] ?? 'all';
            $status = $data['status'] ?? 'all';
            
            \Log::info("Request type: {$requestType}");
            
            // Build filters like TestController
            $status_array = '';
            $request_type_array = '';
            $sub_type_array = '';
            $practice = [0];
            
            if($requestType == 'all'){
                $request_type_array = ['service','academic','enquiry'];
            }else{
                $request_type_array =  [$requestType];
            }

            if($requestType == 'service'){
                if($subType == 'all'){
                    $sub_type_array = ['BreakDown Call','Service Support'];
                }else{
                    $sub_type_array =  [$subType];
                }

                if($status == 'all'){
                    $status_array = ['Assigned', 'Attended', 'Received', 'Re-assigned', 'Received_At_Repair_Center', 'Quotation_Prepared', 'PO_Received', 'Repair_Started', 'Repair_Completed', 'Ready_To_Dispatch', 'Dispatched','Closed'];
                }else{
                    if($status == 'sc'){
                        $status_array =  ['Quotation_Prepared','Received_At_Repair_Center','PO_Received','Repair_Started','Repair_Completed','Ready_To_Dispatch','Dispatched'];
                    }elseif($status == 'received'){
                        $status_array =  ['Received'];
                    }elseif($status == 'closed'){
                        $status_array =  ['Closed'];
                    }elseif($status == 'assigned'){
                        $status_array =  ['Assigned','Re-assigned'];
                    }elseif($status == 'attended'){
                        $status_array =  ['Attended'];
                    }elseif($status == 'rarp'){
                        $status_array =  ['Received_At_Repair_Center'];
                    }elseif($status == 'qp'){
                        $status_array =  ['Quotation_Prepared'];
                    }elseif($status == 'por'){
                        $status_array =  ['PO_Received'];
                    }elseif($status == 'rs'){
                        $status_array =  ['Repair_Started'];
                    }elseif($status == 'rc'){
                        $status_array =  ['Repair_Completed'];
                    }elseif($status == 'rtd'){
                        $status_array =  ['Ready_To_Dispatch'];
                    }elseif($status == 'dispatched'){
                        $status_array =  ['Dispatched'];
                    }
                }
                $practice = [0];
            }elseif($requestType == 'academic'){
                if($subType == 'all'){
                    $sub_type_array = ['Conference','Training','Clinical Info'];
                }else{
                    $sub_type_array =  [$subType];
                }

                if($status == 'all'){
                    $status_array = ['Received','Assigned','Attended','Closed'];
                }else{
                    $status_array =  [$status];
                }
                $practice = [0];
            }elseif($requestType == 'enquiry'){
                if($subType == 'all'){
                    $sub_type_array = ['Product Info','Demonstration','Quotations'];
                }else{
                    $sub_type_array =  [$subType];
                }

                if($status == 'all'){
                    $status_array = ['Received','Assigned','Attended','Closed'];
                }else{
                    $status_array =  [$status];
                }
                $practice = [0];
            }else{
                $sub_type_array = ['BreakDown Call','Service Support','Conference','Training','Clinical Info', 'Product Info','Demonstration','Quotations'];
                $status_array = ['Assigned', 'Attended', 'Received', 'Re-assigned', 'Received_At_Repair_Center', 'Quotation_Prepared', 'PO_Received', 'Repair_Started', 'Repair_Completed', 'Ready_To_Dispatch', 'Dispatched','Closed'];
                $practice = [0,1];
            }

            // Get archive data with relationships
            $archive_data = ArchiveServiceRequests::
                whereIn('request_type', $request_type_array)
                ->whereIn('sub_type', $sub_type_array)
                ->whereIn('status', $status_array)
                ->with(['statusTimelineData', 'customer', 'hospital', 'departmentData'])
                ->whereBetween('created_at', [$fromDate, $toDate])
                ->whereIn('is_practice', $practice)
                ->orderBy('id','asc')
                ->get();

            // Get current data with relationships
            $current_data = ServiceRequests::
                whereIn('request_type', $request_type_array)
                ->whereIn('sub_type', $sub_type_array)
                ->whereIn('status', $status_array)
                ->with(['statusTimelineData', 'customer', 'hospital', 'departmentData'])
                ->whereBetween('created_at', [$fromDate, $toDate])
                ->whereIn('is_practice', $practice)
                ->orderBy('id','asc')
                ->get();

            // Merge both collections
            $query = $archive_data->merge($current_data);
            
            \Log::info('Query result count: ' . $query->count());
            $fileName = 'request_data_' . now()->format('Y-m-d_His') . '.xlsx';
            Excel::store(new \App\Exports\RequestDataExport($query), $fileName, 'public');
            
            $ccEmails = [];
            if (!empty($data['cc_email'])) {
                $ccEmails = array_map('trim', explode(',', $data['cc_email']));
            }
            $mail = Mail::to($data['email']);
            if (!empty($ccEmails)) {
                $mail->cc($ccEmails);
            }
            $mail->send(new ArchiveRequestDataMail($fileName));
            Notification::make()->title('Success')->body('Email sent successfully')->success()->send();
        } catch (\Exception $e) {
            \Log::error('Error: ' . $e->getMessage());
            throw $e;
        }
    }
    private function downloadRequestData(array $data) {
        try {
            ini_set('max_execution_time', -1);
            ini_set('memory_limit', -1);
            
            $fromDate = Carbon::parse($data['from_date'])->startOfDay();
            $toDate = Carbon::parse($data['to_date'])->endOfDay();
            
            $requestType = $data['request_type'] ?? 'all';
            $subType = $data['sub_type'] ?? 'all';
            $status = $data['status'] ?? 'all';
            
            // Build filters like TestController
            $status_array = '';
            $request_type_array = '';
            $sub_type_array = '';
            $practice = [0];
            
            if($requestType == 'all'){
                $request_type_array = ['service','academic','enquiry'];
            }else{
                $request_type_array =  [$requestType];
            }

            if($requestType == 'service'){
                if($subType == 'all'){
                    $sub_type_array = ['BreakDown Call','Service Support'];
                }else{
                    $sub_type_array =  [$subType];
                }

                if($status == 'all'){
                    $status_array = ['Assigned', 'Attended', 'Received', 'Re-assigned', 'Received_At_Repair_Center', 'Quotation_Prepared', 'PO_Received', 'Repair_Started', 'Repair_Completed', 'Ready_To_Dispatch', 'Dispatched','Closed'];
                }else{
                    if($status == 'sc'){
                        $status_array =  ['Quotation_Prepared','Received_At_Repair_Center','PO_Received','Repair_Started','Repair_Completed','Ready_To_Dispatch','Dispatched'];
                    }elseif($status == 'received'){
                        $status_array =  ['Received'];
                    }elseif($status == 'closed'){
                        $status_array =  ['Closed'];
                    }elseif($status == 'assigned'){
                        $status_array =  ['Assigned','Re-assigned'];
                    }elseif($status == 'attended'){
                        $status_array =  ['Attended'];
                    }elseif($status == 'rarp'){
                        $status_array =  ['Received_At_Repair_Center'];
                    }elseif($status == 'qp'){
                        $status_array =  ['Quotation_Prepared'];
                    }elseif($status == 'por'){
                        $status_array =  ['PO_Received'];
                    }elseif($status == 'rs'){
                        $status_array =  ['Repair_Started'];
                    }elseif($status == 'rc'){
                        $status_array =  ['Repair_Completed'];
                    }elseif($status == 'rtd'){
                        $status_array =  ['Ready_To_Dispatch'];
                    }elseif($status == 'dispatched'){
                        $status_array =  ['Dispatched'];
                    }
                }
                $practice = [0];
            }elseif($requestType == 'academic'){
                if($subType == 'all'){
                    $sub_type_array = ['Conference','Training','Clinical Info'];
                }else{
                    $sub_type_array =  [$subType];
                }

                if($status == 'all'){
                    $status_array = ['Received','Assigned','Attended','Closed'];
                }else{
                    $status_array =  [$status];
                }
                $practice = [0];
            }elseif($requestType == 'enquiry'){
                if($subType == 'all'){
                    $sub_type_array = ['Product Info','Demonstration','Quotations'];
                }else{
                    $sub_type_array =  [$subType];
                }

                if($status == 'all'){
                    $status_array = ['Received','Assigned','Attended','Closed'];
                }else{
                    $status_array =  [$status];
                }
                $practice = [0];
            }else{
                $sub_type_array = ['BreakDown Call','Service Support','Conference','Training','Clinical Info', 'Product Info','Demonstration','Quotations'];
                $status_array = ['Assigned', 'Attended', 'Received', 'Re-assigned', 'Received_At_Repair_Center', 'Quotation_Prepared', 'PO_Received', 'Repair_Started', 'Repair_Completed', 'Ready_To_Dispatch', 'Dispatched','Closed'];
                $practice = [0,1];
            }

            // Get archive data with relationships
            $archive_data = ArchiveServiceRequests::
                whereIn('request_type', $request_type_array)
                ->whereIn('sub_type', $sub_type_array)
                ->whereIn('status', $status_array)
                ->with(['statusTimelineData', 'customer', 'hospital', 'departmentData'])
                ->whereBetween('created_at', [$fromDate, $toDate])
                ->whereIn('is_practice', $practice)
                ->orderBy('id','asc')
                ->get();

            // Get current data with relationships
            $current_data = ServiceRequests::
                whereIn('request_type', $request_type_array)
                ->whereIn('sub_type', $sub_type_array)
                ->whereIn('status', $status_array)
                ->with(['statusTimelineData', 'customer', 'hospital', 'departmentData'])
                ->whereBetween('created_at', [$fromDate, $toDate])
                ->whereIn('is_practice', $practice)
                ->orderBy('id','asc')
                ->get();

            // Merge both collections
            $query = $archive_data->merge($current_data);
            
            Notification::make()->title('Success')->body('Download started')->success()->send();
            return Excel::download(new \App\Exports\RequestDataExport($query), 'request_data_' . now()->format('Y-m-d_His') . '.xlsx');
        } catch (\Exception $e) {
            \Log::error('Error: ' . $e->getMessage());
            throw $e;
        }
    }
    private function sendCustomerData(array $data): void {
        try {
            ini_set('max_execution_time', -1);
            ini_set('memory_limit', -1);
            
            $fromDate = Carbon::parse($data['from_date'])->startOfDay();
            $toDate = Carbon::parse($data['to_date'])->endOfDay();
            
            $customers = Customers::whereBetween('created_at', [$fromDate, $toDate])
                ->select('id', 'sap_customer_id', 'customer_id', 'title', 'first_name', 'last_name', 'mobile_number', 'email', 'is_verified', 'otp_code', 'hospital_id', 'platform', 'app_version', 'created_at', 'updated_at')
                ->where('email', 'NOT LIKE', '%@olympus.com%')
                ->get();
                
            $fileName = 'customer_data_' . now()->format('Y-m-d_His') . '.xlsx';
            Excel::store(new \App\Exports\CustomerDataExport($customers), $fileName, 'public');
            
            $ccEmails = array_map('trim', explode(',', $data['cc_email']));
            Mail::to($ccEmails)->send(new ArchiveCustomerDataMail($fileName));
            
            Notification::make()->title('Success')->body('Customer data email sent successfully')->success()->send();
        } catch (\Exception $e) {
            \Log::error('Error: ' . $e->getMessage());
            throw $e;
        }
    }
}
