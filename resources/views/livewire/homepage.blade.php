<div class="my-auto" x-data="{
    activeMenu: null,
    resetTimeout: null,
    idleThreshold: 5000,
    lastActivity: Date.now(),
    welcomeMessage: 'Selamat datang di {{ config('app.voice_hospital_name') }}. Silahkan pilih menu yang tersedia.',
    init() {
        const onActivity = () => {
            const idle = Date.now() - this.lastActivity;
            this.lastActivity = Date.now();
            if (idle >= this.idleThreshold) {
                $dispatch('speak', { text: this.welcomeMessage });
            }
        };
        document.addEventListener('mousemove', onActivity);
        document.addEventListener('click', onActivity);
        document.addEventListener('touchstart', onActivity);
        $nextTick(() => { $dispatch('speak', { text: this.welcomeMessage }); });
    },
    handleMenu(index, title, description, href) {
        if (this.activeMenu === index) {
            this.activeMenu = null;
            clearTimeout(this.resetTimeout);
            Livewire.navigate(href);
            return;
        }
        this.activeMenu = index;
        $dispatch('speak-stop');
        $dispatch('speak', { text: title + '. ' + description + '. Tekan sekali lagi untuk melanjutkan.' });
        clearTimeout(this.resetTimeout);
    }
}"
    @speak-ended.window="if (activeMenu !== null) { clearTimeout(resetTimeout); resetTimeout = setTimeout(() => { activeMenu = null; }, 5000); }">
    <div class="grid grid-cols-2 gap-4">
        @foreach ($menus as $index => $menu)
            <flux:button variant="ghost" @class([
                'drop-shadow-xl relative overflow-hidden h-36 flex flex-col rounded-xl justify-center items-center text-center inset-0 bg-gradient-to-br from-primary-700 to-primary-500 hover:to-yellow-300 active:to-yellow-300 backdrop-blur-2xl backdrop-filter transition-all duration-300',
                !$menu['status'] ? 'opacity-50 pointer-events-none' : '',
            ])
                @click="handleMenu({{ $index }}, '{{ addslashes($menu['title']) }}', '{{ addslashes($menu['description']) }}', '{{ $menu['href'] }}')"
                ::class="activeMenu === {{ $index }} && 'ring-4 ring-secondary-300 !bg-yellow-400/20 scale-[1.03] shadow-[0_0_24px_4px_rgba(255,247,0,0.35)]'">
                <flux:heading class="!text-4xl uppercase !text-secondary-300">
                    {{ $menu['title'] }}
                </flux:heading>
                <flux:text class="text-white font-light text-wrap">
                    {{ $menu['description'] }}
                </flux:text>
                <flux:icon :name="$menu['icon']"
                    class="absolute size-36 -left-6 -z-10 text-white/10 top-1/2 -translate-y-1/2 h-full rotate-6" />

                {{-- Badge "ketuk lagi" muncul saat klik pertama --}}
                <div x-show="activeMenu === {{ $index }}"
                    x-transition:enter="transition ease-out duration-300"
                    x-transition:enter-start="opacity-0 translate-y-3 scale-90"
                    x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                    x-transition:leave="transition ease-in duration-150"
                    x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0"
                    class="absolute bottom-2 left-0 right-0 flex justify-center pointer-events-none">
                    <span class="inline-flex items-center gap-1.5 bg-secondary-300 text-primary-900 text-xs font-bold px-3 py-1 rounded-full animate-bounce shadow-lg">
                        <svg class="size-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 9l3 3-3 3M5 12h12"/></svg>
                        Ketuk lagi untuk lanjut
                    </span>
                </div>
            </flux:button>
        @endforeach
        {{-- Hint text klik 1x / 2x --}}
        <div class="col-span-2 text-center text-white/60 text-sm py-1">
            Ketuk tombol <span class="text-secondary-300 font-semibold">1x</span> untuk mendengar informasi &nbsp;·&nbsp; Ketuk <span class="text-secondary-300 font-semibold">2x</span> untuk melanjutkan
        </div>

        <flux:modal.trigger name="instruction">
            <flux:button variant="ghost" flux:modal="instruction"
                class="drop-shadow-xl col-span-2 relative overflow-hidden h-36 flex flex-col rounded-xl bg-gradient-to-br from-primary-700 to-primary-500 hover:to-yellow-300 active:to-yellow-300 justify-center items-center text-center">
                <flux:heading class="!text-4xl uppercase !text-secondary-300">
                    Pusat Bantuan
                </flux:heading>
                <flux:text class="text-white font-light text-wrap">
                    Jika Anda mengalami kesulitan dalam menggunakan sistem, silakan hubungi petugas kami atau dapat
                    mengklik
                    tombol ini untuk menampilkan beberapa petunjuk yang mungkin membantu Anda.
                </flux:text>
                <flux:icon.exclamation-triangle
                    class="absolute size-36 -left-6 -z-10 text-white/10 top-1/2 -translate-y-1/2 h-full rotate-6" />
            </flux:button>
        </flux:modal.trigger>
    </div>

    <flux:modal name="instruction" class="w-full max-w-6xl" x-data="{
        activeTab: null,
        introText: 'Panduan Penggunaan Aplikasi Pendaftaran Mandiri. Tersedia enam panduan: Antrean Loket, Antrean Poli, Check In JKN, Cek Kepesertaan BPJS, Antrean Farmasi, dan Bantuan. Silahkan pilih salah satu untuk mendengar panduan selengkapnya.',
        voiceTexts: {
            'loket': 'Antrean Loket. Untuk pasien baru yang belum pernah terdaftar di rumah sakit, atau pasien lama dengan data yang tidak sesuai. Cara menggunakan: Pilih tombol Antrean Loket. Sistem akan memberikan nomor antrean otomatis. Tunggu nomor antrean Anda dipanggil. Datang ke loket pendaftaran dengan membawa kartu identitas dan dokumen pendukung.',
            'lama': 'Antrean Poli. Untuk pasien yang sudah pernah berobat dan memiliki Nomor Rekam Medis. Cara menggunakan: Pilih tombol Antrean Poli. Masukkan Nomor Rekam Medis atau NIK. Sistem akan menampilkan data diri Anda. Periksa dan konfirmasi data. Pilih poli atau dokter yang dituju. Untuk pasien BPJS, masukkan nomor kartu BPJS atau NIK. Selesaikan proses pendaftaran. Cetak bukti pendaftaran dan tunggu dipanggil.',
            'checkin': 'Check In JKN. Untuk pasien BPJS yang sudah mendaftar melalui aplikasi Mobile JKN dan memiliki kode booking. Cara menggunakan: Buka aplikasi Mobile JKN. Daftar antrean online sesuai poli dan jadwal dokter. Dapatkan kode booking. Di aplikasi pendaftaran mandiri, pilih Check In JKN. Masukkan kode booking yang telah didapat. Sistem akan memvalidasi data Anda. Selesaikan proses check in. Catatan penting: Pastikan datang sesuai jadwal. Check in paling cepat 30 menit sebelum jadwal praktek dimulai.',
            'bpjs': 'Cek Kepesertaan BPJS. Untuk pasien BPJS yang ingin memastikan status kepesertaan aktif, mengecek rujukan, atau surat kontrol. Cara menggunakan: Pilih tombol Cek Kepesertaan BPJS. Masukkan Nomor Kartu BPJS atau NIK. Sistem akan menampilkan status kepesertaan, hak kelas rawat, daftar rujukan, surat kontrol, dan status biometrik. Pastikan kepesertaan dalam status aktif dan memiliki rujukan yang valid.',
            'farmasi': 'Antrean Farmasi. Untuk pasien yang sudah selesai pemeriksaan dokter dan mendapatkan resep obat. Cara menggunakan: Pilih tombol Antrean Farmasi. Masukkan Kode Booking atau Nomor Rawat, 15 karakter, dimulai huruf A, B, atau D. Sistem akan menampilkan data pasien dan resep Anda. Klik tombol Ambil Antrean Farmasi. Sistem akan mencetak bukti antrean. Tunggu nomor antrean dipanggil di layar farmasi.',
            'bantuan': 'Bantuan Lebih Lanjut. Jika Anda mengalami kesulitan, hubungi petugas pendaftaran yang bertugas atau datang langsung ke loket informasi. Dokumen yang biasanya dibutuhkan: Kartu identitas KTP atau KK, Kartu BPJS jika ada, Surat rujukan jika dari fasilitas kesehatan lain, dan Surat kontrol jika kontrol rutin.'
        },
        switchTab(tab) {
            this.activeTab = tab;
            $dispatch('speak-stop');
            $dispatch('speak', { text: this.voiceTexts[tab] });
        }
    }"
        x-on:modal-show.window="activeTab = null; $dispatch('speak-stop'); $dispatch('speak', { text: introText })"
        x-on:close="$dispatch('speak-stop')">
        <flux:heading size="lg" class="mb-4">Panduan Penggunaan Aplikasi Pendaftaran Mandiri</flux:heading>

        {{-- Tab Navigation --}}
        <div class="flex flex-wrap gap-2 mb-4 border-b border-gray-200 pb-3">
            <template
                x-for="[key, label] in [['loket','Antrean Loket'],['lama','Antrean Poli'],['checkin','Check In JKN'],['bpjs','Cek Kepesertaan'],['farmasi','Antrean Farmasi'],['bantuan','Bantuan']]">
                <button @click="switchTab(key)"
                    :class="activeTab === key ? 'bg-primary-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'"
                    class="px-4 py-2 rounded-lg font-semibold text-sm transition-colors" x-text="label">
                </button>
            </template>
        </div>

        {{-- Tab Content --}}
        <div class="max-h-[60vh] overflow-y-auto pr-2">
            {{-- Tab: Antrean Loket --}}
            <div x-show="activeTab === 'loket'" x-cloak>
                <flux:callout icon="queue-list" variant="success">
                    <flux:heading size="base" class="mb-2">Antrean Loket</flux:heading>
                    <flux:text class="space-y-2">
                        <p class="font-semibold">Untuk siapa:</p>
                        <ul class="list-disc pl-5 space-y-1">
                            <li>Pasien baru yang belum pernah terdaftar di rumah sakit</li>
                            <li>Pasien lama dengan data yang tidak sesuai atau bermasalah</li>
                        </ul>
                        <p class="font-semibold mt-3">Cara menggunakan:</p>
                        <ol class="list-decimal pl-5 space-y-1">
                            <li>Pilih tombol "Antrean Loket"</li>
                            <li>Sistem akan memberikan nomor antrean otomatis</li>
                            <li>Tunggu nomor antrean Anda dipanggil</li>
                            <li>Datang ke loket pendaftaran dengan membawa kartu identitas dan dokumen pendukung</li>
                        </ol>
                    </flux:text>
                </flux:callout>
            </div>

            {{-- Tab: Antrean Poli --}}
            <div x-show="activeTab === 'lama'" x-cloak>
                <flux:callout icon="user-group" variant="info">
                    <flux:heading size="base" class="mb-2">Antrean Poli</flux:heading>
                    <flux:text class="space-y-2">
                        <p class="font-semibold">Untuk siapa:</p>
                        <ul class="list-disc pl-5 space-y-1">
                            <li>Pasien yang sudah pernah berobat di rumah sakit ini</li>
                            <li>Sudah memiliki Nomor Rekam Medis</li>
                            <li>Data di sistem masih valid</li>
                        </ul>
                        <p class="font-semibold mt-3">Cara menggunakan:</p>
                        <ol class="list-decimal pl-5 space-y-1">
                            <li>Pilih tombol "Antrean Poli"</li>
                            <li>Masukkan Nomor Rekam Medis atau NIK/No. KTP Anda</li>
                            <li>Sistem akan menampilkan data diri Anda</li>
                            <li>Periksa dan konfirmasi data</li>
                            <li>Pilih poli/dokter yang dituju</li>
                            <li>Untuk pasien BPJS, masukkan nomor kartu BPJS atau NIK</li>
                            <li>Sistem akan melakukan validasi kepesertaan dan rujukan</li>
                            <li>Selesaikan proses pendaftaran</li>
                            <li>Cetak bukti pendaftaran dan tunggu dipanggil</li>
                        </ol>
                    </flux:text>
                </flux:callout>
            </div>

            {{-- Tab: Check In JKN --}}
            <div x-show="activeTab === 'checkin'" x-cloak>
                <flux:callout icon="finger-print" variant="info">
                    <flux:heading size="base" class="mb-2">Check In JKN</flux:heading>
                    <flux:text class="space-y-2">
                        <p class="font-semibold">Untuk siapa:</p>
                        <ul class="list-disc pl-5 space-y-1">
                            <li>Pasien BPJS yang sudah mendaftar melalui aplikasi Mobile JKN</li>
                            <li>Sudah memiliki kode booking dari Mobile JKN</li>
                        </ul>
                        <p class="font-semibold mt-3">Cara menggunakan:</p>
                        <ol class="list-decimal pl-5 space-y-1">
                            <li>Buka aplikasi Mobile JKN terlebih dahulu</li>
                            <li>Daftar antrean online sesuai poli dan jadwal dokter</li>
                            <li>Dapatkan kode booking (format: B20240101000001)</li>
                            <li>Di aplikasi pendaftaran mandiri, pilih "Check In JKN"</li>
                            <li>Masukkan kode booking yang telah didapat</li>
                            <li>Sistem akan memvalidasi data Anda</li>
                            <li>Konfirmasi nomor telepon jika diperlukan</li>
                            <li>Selesaikan proses check in</li>
                            <li>Antrean Anda akan terdaftar di sistem rumah sakit</li>
                        </ol>
                        <p class="font-semibold mt-3 text-amber-700">Catatan Penting:</p>
                        <ul class="list-disc pl-5 space-y-1 text-amber-700">
                            <li>Pastikan Anda datang sesuai jadwal yang sudah didaftarkan</li>
                            <li>Check in paling cepat 30 menit sebelum jadwal praktek dimulai dan paling lama sebelum
                                jadwal praktek berakhir</li>
                            <li>Jika melewati jadwal, silakan ambil antrean baru</li>
                        </ul>
                    </flux:text>
                </flux:callout>
            </div>

            {{-- Tab: Cek Kepesertaan BPJS --}}
            <div x-show="activeTab === 'bpjs'" x-cloak>
                <flux:callout icon="shield-check" variant="warning">
                    <flux:heading size="base" class="mb-2">Cek Kepesertaan BPJS</flux:heading>
                    <flux:text class="space-y-2">
                        <p class="font-semibold">Untuk siapa:</p>
                        <ul class="list-disc pl-5 space-y-1">
                            <li>Pasien BPJS yang ingin memastikan status kepesertaan aktif</li>
                            <li>Ingin mengecek rujukan yang masih berlaku</li>
                            <li>Ingin mengecek surat kontrol</li>
                        </ul>
                        <p class="font-semibold mt-3">Cara menggunakan:</p>
                        <ol class="list-decimal pl-5 space-y-1">
                            <li>Pilih tombol "Cek Kepesertaan BPJS"</li>
                            <li>Masukkan Nomor Kartu BPJS atau NIK</li>
                            <li>Sistem akan menampilkan:</li>
                            <ul class="list-disc pl-8 mt-1">
                                <li>Status kepesertaan (Aktif/Tidak Aktif)</li>
                                <li>Hak kelas rawat</li>
                                <li>Daftar rujukan yang masih berlaku</li>
                                <li>Surat kontrol yang tersedia</li>
                                <li>Status biometrik/fingerprint</li>
                            </ul>
                            <li>Periksa informasi dengan teliti</li>
                            <li>Jika ada masalah, hubungi petugas atau kantor BPJS</li>
                        </ol>
                        <p class="font-semibold mt-3 text-red-700">Hal yang Perlu Diperhatikan:</p>
                        <ul class="list-disc pl-5 space-y-1 text-red-700">
                            <li>Pastikan kepesertaan dalam status aktif</li>
                            <li>Pastikan memiliki rujukan yang valid untuk poli yang dituju</li>
                            <li>Untuk kontrol, pastikan memiliki surat kontrol yang masih berlaku</li>
                            <li>Jika belum fingerprint, wajib melakukan di loket pendaftaran</li>
                        </ul>
                    </flux:text>
                </flux:callout>
            </div>

            {{-- Tab: Antrean Farmasi --}}
            <div x-show="activeTab === 'farmasi'" x-cloak>
                <flux:callout icon="beaker" variant="primary">
                    <flux:heading size="base" class="mb-2">Antrean Farmasi</flux:heading>
                    <flux:text class="space-y-2">
                        <p class="font-semibold">Untuk siapa:</p>
                        <ul class="list-disc pl-5 space-y-1">
                            <li>Pasien yang sudah selesai pemeriksaan dokter</li>
                            <li>Sudah mendapatkan resep obat dari dokter</li>
                            <li>Ingin mengambil nomor antrean farmasi untuk pengambilan obat</li>
                        </ul>
                        <p class="font-semibold mt-3">Cara menggunakan:</p>
                        <ol class="list-decimal pl-5 space-y-1">
                            <li>Pilih tombol "Antrean Farmasi"</li>
                            <li>Masukkan Kode Booking/No. Rawat (15 karakter, dimulai A/B/D)</li>
                            <li>Contoh kode booking: D20251124000001</li>
                            <li>Sistem akan menampilkan data pasien dan resep Anda</li>
                            <li>Sistem akan menampilkan informasi:</li>
                            <ul class="list-disc pl-8 mt-1">
                                <li>Nomor antrean farmasi Anda</li>
                                <li>Jenis resep</li>
                                <li>Tipe resep: Racikan atau Non Racikan</li>
                                <li>Data pasien dan cara bayar</li>
                            </ul>
                            <li>Klik tombol "Ambil Antrean Farmasi"</li>
                            <li>Sistem akan mencetak bukti antrean farmasi</li>
                            <li>Tunggu nomor antrean Anda dipanggil di layar farmasi</li>
                            <li>Datang ke loket farmasi dengan membawa bukti antrean</li>
                        </ol>
                        <p class="font-semibold mt-3 text-blue-700">Informasi Jenis Resep:</p>
                        <ul class="list-disc pl-5 space-y-1 text-blue-700">
                            <li><strong>Resep Racikan:</strong> Resep yang memerlukan peracikan obat khusus, waktu
                                tunggu lebih lama</li>
                            <li><strong>Resep Non Racikan:</strong> Resep obat jadi, waktu tunggu lebih cepat</li>
                            <li>Estimasi waktu tunggu akan berbeda sesuai jenis resep</li>
                        </ul>
                        <p class="font-semibold mt-3 text-amber-700">Catatan Penting:</p>
                        <ul class="list-disc pl-5 space-y-1 text-amber-700">
                            <li>Pastikan Anda sudah selesai pemeriksaan dengan dokter</li>
                            <li>Pastikan dokter sudah memasukkan resep ke sistem</li>
                            <li>Simpan baik-baik bukti antrean farmasi</li>
                            <li>Jika terlewat, tunjukkan bukti antrean ke petugas untuk dipanggil kembali</li>
                        </ul>
                        <p class="font-semibold mt-3 text-red-700">Yang Harus Dibawa:</p>
                        <ul class="list-disc pl-5 space-y-1 text-red-700">
                            <li>Bukti antrean farmasi yang sudah dicetak</li>
                            <li>[Opsional] Kartu identitas atau kartu berobat</li>
                            <li>[Opsional] Kartu BPJS (jika menggunakan BPJS)</li>
                        </ul>
                    </flux:text>
                </flux:callout>
            </div>

            {{-- Tab: Bantuan --}}
            <div x-show="activeTab === 'bantuan'" x-cloak>
                <flux:callout icon="exclamation-triangle" variant="danger">
                    <flux:heading size="base" class="mb-2">Bantuan Lebih Lanjut</flux:heading>
                    <flux:text class="space-y-2">
                        <p>Jika Anda masih mengalami kesulitan atau kendala:</p>
                        <ul class="list-disc pl-5 space-y-1">
                            <li>Hubungi petugas pendaftaran yang bertugas</li>
                            <li>Datang langsung ke loket informasi</li>
                            <li>Pastikan membawa dokumen yang diperlukan</li>
                        </ul>
                        <p class="font-semibold mt-3">Dokumen yang Biasanya Dibutuhkan:</p>
                        <ul class="list-disc pl-5 space-y-1">
                            <li>Kartu identitas (KTP/KK)</li>
                            <li>Kartu BPJS (jika ada)</li>
                            <li>Surat rujukan (jika dari fasilitas kesehatan lain)</li>
                            <li>Surat kontrol (jika kontrol rutin)</li>
                        </ul>
                    </flux:text>
                </flux:callout>
            </div>
        </div>

        <flux:modal.close>
            <flux:button variant="primary" class="mt-6 w-full">
                Tutup Panduan
            </flux:button>
        </flux:modal.close>
    </flux:modal>
</div>
