<div class="space-y-3">
    @if ($patient)
        <flux:callout color="zinc">
            <flux:callout.text>
                <table class="w-full">
                    <tbody>
                        <tr>
                            <td class='font-semibold align-top w-36'>
                                No. Rekam Medis
                            </td>
                            <td>
                                {{ $patient['no_rkm_medis'] }}
                            </td>
                        </tr>
                        <tr>
                            <td class='font-semibold align-top w-36'>
                                No. BPJS
                            </td>
                            <td>
                                {{ $patient['no_peserta'] }}
                            </td>
                        </tr>
                        <tr>
                            <td class='font-semibold align-top w-36'>
                                NIK
                            </td>
                            <td>
                                {{ $patient['no_ktp'] }}
                            </td>
                        </tr>
                        <tr>
                            <td class='font-semibold align-top w-36'>
                                Nama Pasien
                            </td>
                            <td>
                                {{ $patient['nm_pasien'] }} <br />
                                ({{ Magic::getFullAge($patient['tgl_lahir']) }})
                            </td>
                        </tr>
                        <tr>
                            <td class='font-semibold align-top'>
                                Tempat, Tgl Lahir
                            </td>
                            <td>
                                {{ $patient['tmp_lahir'] }},
                                {{ \Carbon\Carbon::parse($patient['tgl_lahir'])->translatedFormat('d F Y') }}
                            </td>
                        </tr>
                        <tr>
                            <td class='font-semibold align-top w-36'>
                                No. HP
                            </td>
                            <td>
                                {{ $patient['no_tlp'] }}
                            </td>
                        </tr>
                    </tbody>
                </table>

                <div class="mt-4">
                    Pastikan data anda di atas benar. Jika terjadi kesalahan, silahkan ambil <strong>Antrean
                        Loket</strong>
                    untuk melakukan perubahan data di Loket Pendaftaran.
                </div>
            </flux:callout.text>
        </flux:callout>

        @if (!$isMRSame && !$hasError)
            <div wire:loading.remove>
                <flux:callout color="rose" icon="exclamation-triangle" heading="Ketidaksesuaian Data"
                    :text="'Kami menemukan ketidaksesuaian No. RM anda dengan yang terdata di BPJS. Harap lakukan perubahan di Loket Pendaftaran. RM BPJS: '
                    .($participantData ? $participantData['peserta']['mr']['noMR'] : '-').
                    ' | RM RS: '.$patient['no_rkm_medis']" />
            </div>
        @endif

        <div wire:loading.block>
            <flux:callout color="zinc" icon="loading" heading="Status Kepesertaan BPJS"
                text="Sedang memuat status kepesertaan..." />
        </div>
        <div wire:loading.remove>
            @if ($hasError)
                <flux:callout variant="danger" icon="exclamation-circle" heading="Koneksi Gagal" inline>
                    <flux:callout.text>
                        <div class="space-y-2">
                            <div>
                                <strong>Kode Error:</strong> {{ $errorCode }}
                            </div>
                            <div>
                                <strong>Pesan:</strong> {{ $errorMessage }}
                            </div>
                            <div class="text-sm text-zinc-400 mt-2">
                                Gagal mengambil data kepesertaan BPJS. Silakan coba lagi atau hubungi petugas jika
                                masalah berlanjut.
                            </div>
                        </div>
                    </flux:callout.text>
                    <x-slot name="actions">
                        <div class="flex w-full gap-2">
                            <flux:button class="flex-1" variant="primary" color="red"
                                wire:click="retryParticipantCheck" wire:loading.attr="disabled" icon="arrow-path">
                                <span wire:loading.remove wire:target="retryParticipantCheck">Coba Lagi</span>
                                <span wire:loading wire:target="retryParticipantCheck">
                                    <flux:icon.loading />
                                </span>
                            </flux:button>
                        </div>
                    </x-slot>
                </flux:callout>
            @elseif ($errorCode >= 200 && $errorCode < 300 && $errorCode != 200)
                {{-- Code 20X (bukan 200): Warning tapi bukan error --}}
                <flux:callout variant="warning" icon="exclamation-triangle" heading="Perhatian" inline>
                    <flux:callout.text>
                        <div class="space-y-1">
                            <div>
                                <strong>Kode:</strong> {{ $errorCode }}
                            </div>
                            <div>
                                <strong>Pesan:</strong> {{ $errorMessage }}
                            </div>
                            <div class="text-sm text-zinc-400 mt-2">
                                Terdapat kondisi khusus pada data kepesertaan Anda. Silakan lanjutkan atau hubungi
                                petugas untuk informasi lebih lanjut.
                            </div>
                        </div>
                    </flux:callout.text>
                    <x-slot name="actions">
                        <div class="flex w-full gap-2">
                            <flux:button class="flex-1" variant="primary" color="yellow"
                                wire:click="retryParticipantCheck" wire:loading.attr="disabled" icon="arrow-path">
                                <span wire:loading.remove wire:target="retryParticipantCheck">Coba Lagi</span>
                                <span wire:loading wire:target="retryParticipantCheck">
                                    <flux:icon.loading />
                                </span>
                            </flux:button>
                        </div>
                    </x-slot>
                </flux:callout>
            @else
                @if (!empty($participantData['peserta']))
                    @if ($participantData['peserta']['statusPeserta']['keterangan'] == 'AKTIF')
                        <flux:callout variant="success" icon="check-circle" heading="Status Kepesertaan BPJS"
                            text="BPJS Kesehatan dapat anda gunakan." inline>
                            <x-slot name="actions">
                                <div class="flex w-full gap-1">
                                    <flux:modal.trigger name="bpjsParticipantCheck">
                                        <flux:button class="flex-1" variant="primary" color="green"
                                            :loading="false">
                                            Lihat Kepesertaan
                                        </flux:button>
                                    </flux:modal.trigger>
                                </div>
                            </x-slot>
                        </flux:callout>
                    @else
                        <flux:callout variant="danger" icon="x-circle" heading="Status Kepesertaan BPJS" inline>
                            <flux:callout.text>
                                {{ $participantData['peserta']['statusPeserta']['keterangan'] }}. Anda hanya dapat
                                mendaftar
                                sebagai
                                <strong>Pasien Umum</strong>.
                            </flux:callout.text>
                            <x-slot name="actions">
                                <div class="flex w-full gap-1">
                                    <flux:modal.trigger name="bpjsParticipantCheck">
                                        <flux:button class="flex-1" variant="primary" color="red"
                                            :loading="false">
                                            Lihat Kepesertaan
                                        </flux:button>
                                    </flux:modal.trigger>
                                </div>
                            </x-slot>
                        </flux:callout>
                    @endif
                @else
                    <flux:callout variant="danger" icon="x-circle" heading="Status Kepesertaan BPJS">
                        <flux:callout.text>
                            Anda tidak terdaftar BPJS Kesehatan. Anda hanya dapat mendaftar sebagai <strong>Pasien
                                Umum</strong>.
                        </flux:callout.text>
                    </flux:callout>
                @endif
            @endif
        </div>

        <div class="flex gap-3">
            @if ($isNewPatientButton)
                <flux:button class="flex-1" :href="route('new-patient')" variant="primary" color="red" wire:navigate>
                    Antrean Loket
                </flux:button>
            @endif
            <flux:button variant="primary" color="yellow" class="flex-1"
                x-on:click="$flux.modal('participantData').close()">
                Bukan anda?
            </flux:button>
        </div>

        <flux:button class="w-full" variant="primary" wire:click="next" :disabled="!$isMRSame || $hasError"
            wire:loading.attr="disabled">
            <div wire:loading.remove>
                @if ($hasError)
                    <span class="flex items-center gap-2">
                        <flux:icon.exclamation-circle class="size-5" />
                        Terjadi Kesalahan
                    </span>
                @else
                    Lanjutkan
                @endif
            </div>
            <div wire:loading>
                <flux:icon.loading />
            </div>
        </flux:button>

        <flux:modal name="updatePhoneNumber" class="w-full" :dismissible="false">
            <div class="space-y-3">
                <div>
                    <flux:heading size="lg">Perbarui Nomor HP</flux:heading>
                    <flux:text>Nomor HP anda tidak valid. Silahkan perbarui nomor HP anda untuk melanjutkan
                        pendaftaran. Contoh: 081234567890 (berjumlah 11-13 digit).
                    </flux:text>
                </div>

                <livewire:numpad.numpad-basic updatedTrigger="phone-updated" :$isInvalid enteredTrigger="phone-entered"
                    placeholder="No. HP Aktif" maxLength="13" :value="$newPhoneNumber" />

                <button wire:click="skipPhoneValidation" wire:loading.attr="disabled" wire:target="skipPhoneValidation"
                    class="w-full py-3 px-4 rounded-xl border-2 border-primary-700 bg-primary-700 text-secondary-300 font-semibold text-base uppercase tracking-wide cursor-pointer flex items-center justify-center gap-2.5 transition-all duration-150 hover:bg-primary-700 hover:text-secondary-300 active:bg-primary-800 active:text-secondary-300">
                    Saya tidak punya Nomor HP
                </button>
            </div>
        </flux:modal>

        <flux:modal name="bpjsParticipantCheck" class="w-[720px]">
            <div class="space-y-4">
                <div>
                    <flux:heading size="lg">Data Kepesertaan BPJS</flux:heading>
                    <flux:text class="mt-1">Berikut data kepesertaan BPJS anda.</flux:text>
                </div>

                @if (!empty($participantData['peserta']))
                    <livewire:bpjs-participant-data wire:key="bpjsParticipantData" :$participantData lazy />
                @endif

                <div class="flex gap-3 mt-4">
                    <flux:spacer />
                    <flux:modal.close>
                        <flux:button wire:loading.attr="disabled">Tutup</flux:button>
                    </flux:modal.close>
                    <flux:button wire:loading.attr="disabled" icon="arrow-right-left" variant="primary"
                        wire:click="updatePatientData">
                        Perbarui Rekam Medis
                    </flux:button>
                </div>
            </div>
        </flux:modal>
    @else
        <flux:icon.loading />
    @endif
</div>
