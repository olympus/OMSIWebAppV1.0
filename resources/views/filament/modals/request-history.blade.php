<x-filament::section>
    <x-slot name="heading">
        Request Status Timeline
    </x-slot>

    <div class="overflow-x-auto rounded-xl border border-gray-200 dark:border-gray-700">
        <table class="fi-ta-table w-full divide-y divide-gray-200 dark:divide-gray-700 text-sm">

            <thead class="bg-gray-50 dark:bg-gray-800">
                <tr class="text-left font-semibold text-gray-600 dark:text-gray-300">

                    <th class="px-4 py-3">ID</th>
                    <th class="px-4 py-3">Request ID</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3">Created At</th>
                    <th class="px-4 py-3">Updated At</th>

                </tr>
            </thead>

            <tbody class="divide-y divide-gray-100 dark:divide-gray-800" style="padding-left: 20px; margin-left: 20px; text-align: center;">

                @foreach ($history as $entry)

                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-800 transition">

                        <td class="px-4 py-3 pr-12 font-medium">
                            {{ $entry->id }}
                        </td>

                        <td class="px-4 py-3 pr-12">
                            {{ $entry->request_id }}
                        </td>

                        <td class="px-4 py-3 pr-12">

                            <x-filament::badge
                                :color="match($entry->status) {
                                    'Received' => 'gray',
                                    'Assigned' => 'warning',
                                    'Re-assigned' => 'warning',
                                    'Escalated' => 'danger',
                                    'Closed' => 'success',
                                    'Attended' => 'success',
                                    'Repair_Started' => 'info',
                                    'Repair_Completed' => 'success',
                                    'Ready_To_Dispatch' => 'primary',
                                    'Dispatched' => 'success',
                                    'Dispatch' => 'success',
                                    'Quotation Prepared' => 'warning',
                                    'Quotation_Prepared' => 'warning',
                                    'PO Received' => 'info',
                                    'PO_Received' => 'info',
                                    'Received at Repair Center' => 'primary',
                                    'Received_At_Repair_Center' => 'primary', 
                                    default => 'primary' 
                                }"
                            >
                                {{ $entry->status }}
                            </x-filament::badge>

                        </td>

                        <td class="px-4 py-3 pr-12 text-gray-500">
                            {{ $entry->created_at->format('d M Y h:i A') }}
                        </td>

                        <td class="px-4 py-3 pr-12 text-gray-500">
                            {{ $entry->updated_at->format('d M Y h:i A') }}
                        </td>

                    </tr>

                @endforeach

            </tbody>

        </table>
    </div>

</x-filament::section>