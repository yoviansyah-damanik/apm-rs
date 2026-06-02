<div class="flex flex-col h-full gap-3">

    {{-- Header --}}
    <div class="px-1 pt-2 shrink-0 text-center">
        <p class="text-base font-bold uppercase tracking-[0.2em] text-secondary-300 mb-0.5">Sistem</p>
        <h1 class="text-4xl font-black text-white drop-shadow-lg uppercase">Activity Log</h1>
    </div>

    {{-- Main Card --}}
    <div class="flex-1 overflow-hidden rounded-2xl shadow-xl bg-white/90 backdrop-blur-xl flex flex-col">

        {{-- Card Header --}}
        <div class="flex flex-col sm:flex-row sm:items-center gap-3 px-6 py-4 shrink-0 bg-primary-700">
            <div class="flex-1">
                <p class="font-black text-white text-lg leading-tight">Log Aktivitas Sistem</p>
                <p class="text-sm text-white/70">Total {{ $logs->total() }} entri</p>
            </div>

            {{-- Filter --}}
            <div class="flex flex-wrap items-center gap-2">
                <div class="relative">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 size-4 text-white/50 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M17 11A6 6 0 111 11a6 6 0 0116 0z"/>
                    </svg>
                    <input
                        type="text"
                        wire:model.live.debounce.400ms="search"
                        placeholder="Cari event / pesan..."
                        class="pl-9 pr-4 py-2 rounded-xl text-sm bg-white/20 text-white placeholder-white/50 border border-white/20 focus:outline-none focus:ring-2 focus:ring-secondary-300/60 w-48"
                    />
                </div>

                <select wire:model.live="filterType"
                    class="px-3 py-2 rounded-xl text-sm bg-white/20 text-white border border-white/20 focus:outline-none focus:ring-2 focus:ring-secondary-300/60">
                    <option value="" class="text-gray-800">Semua Tipe</option>
                    @foreach ($types as $type)
                        <option value="{{ $type }}" class="text-gray-800">{{ strtoupper($type) }}</option>
                    @endforeach
                </select>

                <select wire:model.live="filterStatus"
                    class="px-3 py-2 rounded-xl text-sm bg-white/20 text-white border border-white/20 focus:outline-none focus:ring-2 focus:ring-secondary-300/60">
                    <option value="" class="text-gray-800">Semua Status</option>
                    <option value="success" class="text-gray-800">Success</option>
                    <option value="error" class="text-gray-800">Error</option>
                </select>

                <input
                    type="date"
                    wire:model.live="filterDate"
                    class="px-3 py-2 rounded-xl text-sm bg-white/20 text-white border border-white/20 focus:outline-none focus:ring-2 focus:ring-secondary-300/60"
                />
            </div>
        </div>

        {{-- Tabel --}}
        <div class="flex-1 overflow-auto">
            <table class="w-full text-left">
                <thead class="sticky top-0 z-10 bg-primary-50 border-b border-primary-100">
                    <tr>
                        <th class="px-6 py-3 text-sm font-bold text-primary-800 uppercase tracking-widest whitespace-nowrap">Waktu</th>
                        <th class="px-6 py-3 text-sm font-bold text-primary-800 uppercase tracking-widest">Tipe</th>
                        <th class="px-6 py-3 text-sm font-bold text-primary-800 uppercase tracking-widest">Event</th>
                        <th class="px-6 py-3 text-sm font-bold text-primary-800 uppercase tracking-widest text-center">Status</th>
                        <th class="px-6 py-3 text-sm font-bold text-primary-800 uppercase tracking-widest">Pesan</th>
                        <th class="px-6 py-3 text-sm font-bold text-primary-800 uppercase tracking-widest text-center">IP</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($logs as $i => $log)
                        <tr class="transition-colors {{ $i % 2 === 0 ? 'bg-white' : 'bg-gray-50/70' }} hover:bg-primary-50">
                            <td class="px-6 py-3.5 whitespace-nowrap">
                                <p class="text-sm font-semibold text-gray-700">{{ $log->created_at->format('d/m/Y') }}</p>
                                <p class="text-xs text-gray-400">{{ $log->created_at->format('H:i:s') }}</p>
                            </td>
                            <td class="px-6 py-3.5">
                                @php
                                    $typeColor = match($log->type) {
                                        'bpjs'        => 'bg-blue-100 text-blue-700 border-blue-200',
                                        'database'    => 'bg-violet-100 text-violet-700 border-violet-200',
                                        'fingerprint' => 'bg-orange-100 text-orange-700 border-orange-200',
                                        'frista'      => 'bg-cyan-100 text-cyan-700 border-cyan-200',
                                        default       => 'bg-gray-100 text-gray-600 border-gray-200',
                                    };
                                @endphp
                                <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-bold border {{ $typeColor }} uppercase">
                                    {{ $log->type }}
                                </span>
                            </td>
                            <td class="px-6 py-3.5">
                                <p class="text-sm font-semibold text-gray-700">{{ $log->event }}</p>
                            </td>
                            <td class="px-6 py-3.5 text-center">
                                @if ($log->status === 'success')
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-primary-100 text-primary-700 text-xs font-bold border border-primary-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-primary-500 animate-pulse"></span>
                                        Success
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-red-100 text-red-600 text-xs font-bold border border-red-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span>
                                        Error
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-3.5 max-w-sm">
                                <p class="text-sm text-gray-600 truncate" title="{{ $log->message }}">
                                    {{ $log->message }}
                                </p>
                                @if ($log->context)
                                    <flux:modal.trigger name="ctx-{{ $log->id }}">
                                        <button class="text-xs text-primary-600 hover:text-primary-800 font-semibold hover:underline mt-0.5">
                                            Lihat detail →
                                        </button>
                                    </flux:modal.trigger>
                                    <flux:modal name="ctx-{{ $log->id }}" class="max-w-2xl">
                                        <flux:heading size="lg" class="mb-3">Detail Context</flux:heading>
                                        <pre class="text-xs bg-gray-50 border border-gray-200 rounded-xl p-4 overflow-x-auto max-h-96 text-gray-700">{{ json_encode($log->context, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                        <flux:modal.close>
                                            <flux:button class="mt-4 w-full" variant="primary">Tutup</flux:button>
                                        </flux:modal.close>
                                    </flux:modal>
                                @endif
                            </td>
                            <td class="px-6 py-3.5 text-center">
                                <span class="text-xs text-gray-400 font-mono">{{ $log->ip_address ?? '-' }}</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-16 text-center">
                                <div class="w-16 h-16 rounded-2xl bg-primary-50 border border-primary-100 flex items-center justify-center mx-auto mb-3">
                                    <flux:icon.inbox class="size-8 text-primary-300" />
                                </div>
                                <p class="font-bold text-gray-500">Belum ada log aktivitas</p>
                                <p class="text-sm text-gray-400 mt-1">Log akan muncul setelah ada aktivitas sistem</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if ($logs->hasPages())
            <div class="shrink-0 px-6 py-3 border-t border-gray-100 bg-primary-50/50">
                {{ $logs->links() }}
            </div>
        @endif

    </div>
</div>
