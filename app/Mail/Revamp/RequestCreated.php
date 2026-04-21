<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class RequestCreated extends Mailable
{
    use Queueable, SerializesModels;

    public $servicerequest;
    public $customer;
    public $assign_request;
    public $request_id;

    public function __construct($request_id, $servicerequest, $customer, $assign_request)
    {
        $this->servicerequest = $servicerequest;
        $this->customer = $customer;
        $this->assign_request = $assign_request;
        $this->request_id = $request_id;
    }

    public function build()
    {
        // Create folder if not exists
        $exportPath = storage_path('app/exports');
        if (!file_exists($exportPath)) {
            mkdir($exportPath, 0755, true);
        }

        // File path
        $fileName = 'ServiceRequests-' . $this->servicerequest->id . '.xls';
        $filePath = $exportPath . '/' . $fileName;

        // Generate simple Excel-compatible CSV content
        $rows = [
            ['Request ID', $this->servicerequest->id],
            ['Request Type', $this->servicerequest->request_type],
            ['Customer Name', $this->customer->first_name . ' ' . $this->customer->last_name],
            ['Assigned Employee', $this->assign_request->name ?? 'N/A'],
            ['Created At', $this->servicerequest->created_at->toDateTimeString()],
        ];

        $file = fopen($filePath, 'w');
        foreach ($rows as $row) {
            fputcsv($file, $row, "\t"); // tab-separated for Excel
        }
        fclose($file);

        return $this
            ->bcc(config('oly.developer_email'))
            ->subject('Olympus My Voice | ' . ucfirst($this->servicerequest->request_type) . ' | ' . $this->servicerequest->cvm_id)
            ->view('emails.request_created_new', ['id' => $this->request_id])
            ->attach($filePath, [
                'as' => 'Request-' . $this->servicerequest->id . '.xls',
                'mime' => 'application/vnd.ms-excel',
            ]);
    }
}
