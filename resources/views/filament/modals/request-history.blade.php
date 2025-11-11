<table class="table-auto w-full text-sm" >
    <thead>
    <tr class="bg-gray-100">
        <th class="px-4 py-2" border="1">ID</th>
        <th class="px-4 py-2">Request Id</th>
        <th class="px-4 py-2">Status</th>
        <th class="px-4 py-2">Created At</th>
        <th class="px-4 py-2">Updated At</th>
    </tr>
    </thead>
    <tbody>
    @foreach ($history as $entry)
        <tr>
            <td class="border px-4 py-2" style="text-align: center;" border="1">{{ $entry->id }}</td>
            <td class="border px-4 py-2" style="text-align: center;">{{ $entry->request_id }}</td>
            <td class="border px-4 py-2" style="text-align: center;">{{ $entry->status }}</td>
            <td class="border px-4 py-2" style="text-align: center;">{{ $entry->created_at->format('d M Y h:i A') }}</td>
            <td class="border px-4 py-2" style="text-align: center;">{{ $entry->updated_at->format('d M Y h:i A') }}</td>
        </tr>
    @endforeach
    </tbody>
</table>
