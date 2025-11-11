<x-filament-panels::page>
    <form wire:submit.prevent="submit" class="space-y-6 max-w-4xl mx-auto">
        <div>
            <label for="attachment" class="block text-sm font-medium text-gray-700">Select ESAS File</label>
            <input
                id="attachment"
                type="file"
                wire:model.live="attachment"
                class="fi-input mt-1 block w-full"
                accept=".xlsx,.xls,.csv,.txt"
                required
            />
            @error('attachment') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
        </div>

        @if($attachment)
            <div class="mt-2 p-2 bg-green-50 border border-green-200 rounded">
                <p class="text-green-700 text-sm">✅ File selected: {{ $attachment->getClientOriginalName() }}</p>
                <p class="text-green-600 text-xs">Size: {{ number_format($attachment->getSize() / 1024, 2) }} KB</p>
            </div>
        @endif

        <div class="flex gap-2">
            <x-filament::button type="submit" :disabled="!$attachment">Submit</x-filament::button>
            <x-filament::button color="gray" wire:click="$set('attachment', null)">Cancel</x-filament::button>
        </div>
    </form>

    @if ($tableData)
        <form wire:submit.prevent="finalizeImport" class="mt-6">
            <div class="section" id="enableScroll">
                <table id='esas_import' class='table table-striped table-dark' style="font-family: arial, sans-serif; border-collapse: collapse; width:100%;">
                    <thead class="bg-gray-100">
                    <tr>
                        <th style="border: 1px solid #dddddd; text-align: left;padding: 8px;">No.</th>
                        <th style="border: 1px solid #dddddd; text-align: left;padding: 8px;">Y/N</th>
                        <th style="border: 1px solid #dddddd; text-align: left;padding: 8px;">Action</th>
                        <th style="border: 1px solid #dddddd; text-align: left;padding: 8px;">CVM Req No</th>
                        <th style="border: 1px solid #dddddd; text-align: left;padding: 8px;">Previous Status</th>
                        <th style="border: 1px solid #dddddd; text-align: left;padding: 8px;">New Status</th>
                        <th style="border: 1px solid #dddddd; text-align: left;padding: 8px;">Trigger</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach ($tableData as $index => $data)
                        <tr style="{{ $data['checked'] == 0 ? 'background-color: #f8f9fa;' : '' }}">
                            <td style="border: 1px solid #dddddd; text-align: left;padding: 8px;">{{ $index + 1 }}</td>
                            <td style="border: 1px solid #dddddd; text-align: left;padding: 8px;">
                                <input type='checkbox' wire:model="selectedRows" value="{{ $data['cvm_req_no'] }}" {{ $data['checked'] > 0 ? 'checked' : '' }}>
                            </td>
                            <td style="border: 1px solid #dddddd; text-align: left;padding: 8px; {{ in_array($data['action'], ['Status Update']) ? 'background-color: yellow;' : (in_array($data['action'], ['CVM Deleted', 'Unknown Status']) ? 'background-color: red;' : '') }}">
                                {{ $data['action'] }}
                            </td>
                            <td style="border: 1px solid #dddddd; text-align: left;padding: 8px;">
                                @if(is_numeric($data['cvm_req_no']) && !str_contains($data['cvm_req_no'], '.'))
                                    <a href="/admin/requests/{{ $data['cvm_req_no'] }}" target="_blank">{{ $data['cvm_req_no'] }}</a>
                                @else
                                    {{ $data['cvm_req_no'] }}
                                @endif
                            </td>
                            <td style="border: 1px solid #dddddd; text-align: left;padding: 8px; {{ $data['previous_status'] != $data['status'] ? 'background-color: #e6ffb3;' : '' }}">
                                {{ $data['previous_status'] }}
                            </td>
                            <td style="border: 1px solid #dddddd; text-align: left;padding: 8px; {{ $data['previous_status'] != $data['status'] ? 'background-color: #e6ffb3;' : '' }}">
                                {{ $data['status'] }}
                            </td>
                            <td style="border: 1px solid #dddddd; text-align: left;padding: 8px;">{{ $data['trigger'] }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-4 flex gap-2">
                <x-filament::button type="submit">Finalize Import</x-filament::button>
                <x-filament::button color="gray" wire:click="$set('tableData', [])">Clear</x-filament::button>
                <button type="button" id="selectAll" class="btn btn-info">Select All</button>
                <button type="button" id="showAlltr" class="btn btn-success">Display Hidden Rows</button>
            </div>

            <div class="container notes" style="background-color: #dcdcdc; border-radius: 10px; margin-top: 20px; width: auto; padding: 15px;">
                <b><ul>Notes:</b>
                    <li>Action where "CVM Deleted" or "Unknown Status" can not be selected and will be ignored</li>
                </ul>
                <b><ul>Meaning of different Action types -</b>
                    <li>Status Update - There is status change</li>
                    <li>Status Unchanged - No status change</li>
                    <li>Status Rollback - Status present in our database is ahead of status imported</li>
                    <li>CVM Deleted - Request not present in our database(most probably deleted due to duplicate request)</li>
                    <li>CVM New - Request not present in our database(most probably new request)</li>
                    <li>Unknown Status - Status not recognized and will be ignored</li>
                    <li>Skip - Status is disabled and not mapped to any status</li>
                </ul>
            </div>
        </form>
    @endif

    @if (session()->has('messages'))
        <div class="mt-6 bg-green-50 border border-green-200 p-4 rounded">
            <h3 class="font-semibold text-green-700 mb-2">Import Summary</h3>
            <ul class="list-disc pl-5 text-green-800">
                @foreach (session('messages') as $msg)
                    <li>{{ $msg[1] }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @push('js')
    <script type='text/javascript'>
    document.addEventListener('DOMContentLoaded', function() {
        // Set header background color
        document.querySelectorAll('#esas_import tr th').forEach(th => {
            th.style.backgroundColor = 'seashell';
        });

        // Select All functionality
        document.getElementById('selectAll')?.addEventListener('click', function() {
            document.querySelectorAll('#esas_import tr td:nth-child(2) input[type="checkbox"]:visible').forEach(checkbox => {
                checkbox.checked = true;
                checkbox.dispatchEvent(new Event('change'));
            });
        });

        // Show All Rows functionality
        document.getElementById('showAlltr')?.addEventListener('click', function() {
            document.querySelectorAll('#esas_import tr').forEach(row => {
                if (getComputedStyle(row).display === 'none') {
                    row.style.display = 'table-row';
                }
            });
        });
    });
    </script>
    @endpush

    @push('css')
    <style>
        table { font-family: arial, sans-serif; border-collapse: collapse; width:100%; }
        td, th { border: 1px solid #dddddd; text-align: left; padding: 8px; }
        tr:nth-child(even) { background-color: #f9f9f9; }
        .enableScroll { overflow-x: scroll; }
        .notes { margin-top: 20px; }
        .btn { margin-left: 10px; padding: 8px 16px; border: none; border-radius: 4px; cursor: pointer; }
        .btn-info { background-color: #17a2b8; color: white; }
        .btn-success { background-color: #28a745; color: white; }
        .btn:hover { opacity: 0.8; }
    </style>
    @endpush
</x-filament-panels::page>