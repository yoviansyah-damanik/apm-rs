<header x-data="clock()" x-init="start()" class="bg-gradient-to-b from-primary-700 to-primary-900">
    <div class="flex items-center justify-between gap-5 px-4 py-2 mx-auto max-w-7xl ">
        <flux:button href="{{ route('home') }}" variant="ghost" wire:navigate class="flex items-center gap-3 !px-0">
            <img src="{{ Vite::image('logo-icon.png') }}" alt="Logo Icon" class="h-[60px]" />
            <div>
                <flux:heading level="3" class="!text-xl !font-bold uppercase text-secondary-300">
                    Anjungan Pendaftaran Mandiri
                </flux:heading>
                <flux:text class="text-base text-white">
                    {{ Setting::get('hospitalName') }}
                </flux:text>
            </div>
        </flux:button>
        <div class="flex flex-col text-end">
            <flux:text class="text-secondary-300 text-3xl font-bold" x-text="time" />
            <flux:text class="text-secondary-300 text-sm" x-text="dayDate" />
        </div>
    </div>
</header>

@push('scripts')
    <script>
        function clock() {
            return {
                time: '',
                dayDate: '',
                start() {
                    this.update()
                    setInterval(() => this.update(), 1000)
                },
                update() {
                    const now = new Date()

                    // Format jam
                    const hours = String(now.getHours()).padStart(2, '0')
                    const minutes = String(now.getMinutes()).padStart(2, '0')
                    const seconds = String(now.getSeconds()).padStart(2, '0')
                    this.time = `${hours}:${minutes}:${seconds}`

                    // Format hari & tanggal
                    const days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu']
                    const months = [
                        'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
                        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
                    ]

                    const dayName = days[now.getDay()]
                    const day = now.getDate()
                    const month = months[now.getMonth()]
                    const year = now.getFullYear()

                    this.dayDate = `${dayName}, ${day} ${month} ${year}`
                }
            }
        }
    </script>
@endpush
