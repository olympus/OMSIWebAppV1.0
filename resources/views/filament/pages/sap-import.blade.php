<x-filament-panels::page>
    <form wire:submit.prevent="submit" class="space-y-6 max-w-4xl mx-auto">
        <div>
            <label for="attachment" class="block text-sm font-medium text-gray-700">Select SAP File</label>
            <input
                id="attachment"
                type="file"
                wire:model.live="attachment"
                class="fi-input mt-1 block w-full"
                accept=".csv,.txt,.xlsx"
                required
            />
            @error('attachment') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
        </div>

        <div class="flex gap-2">
            <x-filament::button type="submit" :disabled="!$attachment">Submit</x-filament::button>
            <x-filament::button color="gray" wire:click="$set('attachment', null)">Cancel</x-filament::button>
        </div>

        @if($attachment)
            <div class="mt-2 p-2 bg-green-50 border border-green-200 rounded">
                <p class="text-green-700 text-sm">✅ File selected: {{ $attachment->getClientOriginalName() }}</p>
                <p class="text-green-600 text-xs">Size: {{ number_format($attachment->getSize() / 1024, 2) }} KB</p>
            </div>
        @endif
    </form>

    @if ($tableData)
        <form wire:submit.prevent="finalizeImport" class="mt-6">
            <table class="table-auto w-full border text-sm">
                <thead class="bg-gray-100">
                <tr>
                    <th>No.</th>
                    <th>Action</th>
                    <th>CVM Req No</th>
                    <th>Imported CVM Col</th>
                    <th>Previous Status</th>
                    <th>New Status</th>
                    <th>SAP ID</th>
                    <th>Type</th>
                    <th>Trigger</th>
                    <th>FSE Code</th>
                    <th>Customer Name</th>
                    <th>Request ID</th>
                    <th>Customer Code</th>
                    <th>Customer City</th>
                    <th>Customer State</th>
                    <th>Product Model</th>
                    <th>Material</th>
                    <th>Serial No</th>
                    <th>Equipment No</th>
                    <th>Description</th>
                </tr>
                </thead>
                <tbody>
                @foreach ($tableData as $index => $data)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $data['action'] }}</td>
                        <td>{{ $data['cvm_req_no'] }}</td>
                        <td>{{ $data['orig_cvm_col'] }}</td>
                        <td>{{ $data['previous_status'] }}</td>
                        <td>{{ $data['status'] }}</td>
                        <td>{{ $data['sap_id'] }}</td>
                        <td>{{ ucfirst($data['type']) }}</td>
                        <td>{{ $data['trigger'] }}</td>
                        <td>{{ $data['fse_code'] }}</td>
                        <td>{{ $data['customer_name'] }}</td>
                        <td>{{ $data['request_id'] }}</td>
                        <td>{{ $data['customer_code'] }}</td>
                        <td>{{ $data['customer_city'] }}</td>
                        <td>{{ $data['customer_state'] }}</td>
                        <td>{{ $data['prod_model_no'] }}</td>
                        <td>{{ $data['prod_material'] }}</td>
                        <td>{{ $data['prod_serial_no'] }}</td>
                        <td>{{ $data['prod_equipment_no'] }}</td>
                        <td>{{ $data['prod_material_description'] }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>

            <div class="mt-4 flex gap-2">
                <x-filament::button type="submit">Finalize Import</x-filament::button>
                <x-filament::button color="gray" wire:click="$set('tableData', [])">Clear</x-filament::button>
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
</x-filament-panels::page>
