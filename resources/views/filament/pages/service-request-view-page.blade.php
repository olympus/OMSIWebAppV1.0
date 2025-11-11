<x-filament::page>
    <x-filament::card>
        <h2 class="text-xl font-bold mb-4">Service Request Details</h2>

        <div class="grid grid-cols-2 gap-4">
            <div><strong>My Voice ID:</strong> {{ $record->cvm_id }}</div>
            <div><strong>Customer Name:</strong> {{ $record->customer->first_name ?? '' }} {{ $record->customer->last_name ?? '' }}</div>
            <div><strong>Hospital:</strong> {{ $record->hospital->hospital_name ?? '' }}</div>
            <div><strong>Department:</strong> {{ $record->departmentData->name ?? '' }}</div>
            <div><strong>Employee:</strong> {{ $record->employeeData->name ?? '' }}</div>
            <div><strong>Request Type:</strong> {{ $record->request_type }}</div>
            <div><strong>Status:</strong> {{ $record->status }}</div>
            <div><strong>Remarks:</strong> {{ $record->remarks }}</div>
            <div><strong>Created At:</strong> {{ $record->created_at }}</div>
            <div><strong>Updated At:</strong> {{ $record->updated_at }}</div>
        </div>
    </x-filament::card>

    <x-filament::card class="mt-6">
        <h2 class="text-xl font-bold mb-4">Status Timeline</h2>
        <ul class="space-y-2">
            @foreach($history as $h)
                <li class="border-b pb-2">
                    <div><strong>{{ $h->status }}</strong></div>
                    <div class="text-sm text-gray-500">{{ $h->created_at }} by {{ $h->updated_by ?? 'System' }}</div>
                    <div>{{ $h->remarks }}</div>
                </li>
            @endforeach
        </ul>
    </x-filament::card>
</x-filament::page>
