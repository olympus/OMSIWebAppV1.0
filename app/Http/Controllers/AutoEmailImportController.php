<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\AutoEmails;
use Illuminate\Support\Facades\DB;

class AutoEmailImportController extends Controller
{
    public function updateFromCsv(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|mimes:csv,txt'
        ]);

        $file = fopen($request->file('csv_file')->getRealPath(), 'r');

        $header = fgetcsv($file); // first row header

        $updatedIds = [];
        $notMatched = [];

        DB::beginTransaction();

        try {
            while (($row = fgetcsv($file)) !== false) {

                $data = array_combine($header, $row);
                //dd($data['to_emails']);
                // exact match query
                $record = AutoEmailList::where('request_type', trim($data['request_type']))
                    ->where('states', trim($data['states']))
                    ->where('departments', trim($data['departments']))
                    ->first(); 
                if ($record) {
                    $record->update([ 
                        'to_emails'     => $data['to_emails'] ?? null,
                        'cc_emails'     => $data['cc_emails'] ?? null,
                        'escalation_1'  => $data['escalation_1'] ?? null,
                        'escalation_2'  => $data['escalation_2'] ?? null,
                        'escalation_3'  => $data['escalation_3'] ?? null,
                        'escalation_4'  => $data['escalation_4'] ?? null,
                    ]);

                    $updatedIds[] = $record->id;
                } else {
                    $notMatched[] = [
                        'request_type' => $data['request_type'],
                        'states' => $data['states'],
                        'departments' => $data['departments'],
                    ];
                }
            }

            DB::commit();

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }

        fclose($file);

        return response()->json([
            'status' => 'success',
            'updated_count' => count($updatedIds),
            'updated_ids' => $updatedIds,
            'not_matched' => $notMatched
        ]);
    }
}
