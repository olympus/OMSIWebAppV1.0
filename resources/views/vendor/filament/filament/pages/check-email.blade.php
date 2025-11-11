<x-filament::page>
    <form wire:submit.prevent="submit" class="space-y-6">
        <div>
            <label class="font-semibold">Enter Email</label>
            <input type="email"
                   wire:model.defer="email"
                   class="fi-input mt-1 w-full"
                   placeholder="Enter email address" />
        </div>

        <div class="flex gap-2">
            <x-filament::button type="submit">Check</x-filament::button>
            <x-filament::button color="gray" wire:click="$set('email', null)">Cancel</x-filament::button>
        </div>
    </form>

    @if ($errorsList)
        <div class="mt-4 p-4 bg-red-50 border border-red-200 rounded">
            <h3 class="font-semibold text-red-600">Results:</h3>
            <ul class="list-disc pl-5 text-red-700">
                @foreach ($errorsList as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if ($message)
        <div class="mt-4 p-4 bg-green-50 border border-green-200 rounded text-green-700">
            {{ $message }}
        </div>
    @endif
</x-filament::page>
