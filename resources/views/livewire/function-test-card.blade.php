@php
    $result    = $resultsData[$key] ?? null;
    $isSuccess = ($result['status'] ?? null) === 'success';
    $isError   = ($result['status'] ?? null) === 'error';
@endphp

<div
    wire:click="{{ $method }}"
    wire:loading.class="opacity-60 pointer-events-none"
    wire:target="{{ $method }}"
    class="relative group rounded-2xl border-2 p-5 cursor-pointer transition-all duration-200 hover:shadow-lg hover:-translate-y-0.5 active:scale-[0.98] select-none
        {{ $isSuccess ? 'border-primary-300 bg-primary-50' : ($isError ? 'border-red-300 bg-red-50' : 'border-gray-200 bg-white hover:border-primary-300') }}"
>
    {{-- Loading overlay --}}
    <div wire:loading wire:target="{{ $method }}"
         class="absolute inset-0 flex items-center justify-center rounded-2xl bg-white/70 z-10">
        <svg class="size-7 animate-spin text-primary-600" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
        </svg>
    </div>

    <div class="flex items-start gap-4">
        {{-- Icon --}}
        <div class="shrink-0 w-12 h-12 rounded-xl flex items-center justify-center transition-colors
            {{ $isSuccess ? 'bg-primary-100' : ($isError ? 'bg-red-100' : 'bg-primary-50 group-hover:bg-primary-100') }}">
            <flux:icon
                :name="$icon"
                class="size-6 {{ $isSuccess ? 'text-primary-600' : ($isError ? 'text-red-500' : 'text-primary-400') }}"
            />
        </div>

        {{-- Content --}}
        <div class="flex-1 min-w-0">
            <div class="flex items-center gap-2 mb-0.5">
                <p class="font-black text-gray-800 text-base leading-tight">{{ $label }}</p>
                @if ($isSuccess)
                    <span class="inline-flex items-center gap-1 text-xs font-bold px-2 py-0.5 rounded-full bg-primary-600 text-white">
                        <svg class="size-3" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                        </svg>
                        OK
                    </span>
                @elseif ($isError)
                    <span class="inline-flex items-center gap-1 text-xs font-bold px-2 py-0.5 rounded-full bg-red-500 text-white">
                        <svg class="size-3" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                        </svg>
                        Gagal
                    </span>
                @endif
            </div>
            <p class="text-xs text-gray-400 mb-2">{{ $desc }}</p>

            @if ($result)
                <p class="text-sm font-medium leading-snug line-clamp-2 {{ $isSuccess ? 'text-primary-700' : 'text-red-600' }}">
                    {{ $result['message'] }}
                </p>
                <p class="text-xs text-gray-300 mt-1.5">{{ $result['time'] }}</p>
            @else
                <p class="text-xs text-gray-300 italic">Belum diuji — klik untuk menguji</p>
            @endif
        </div>
    </div>
</div>
