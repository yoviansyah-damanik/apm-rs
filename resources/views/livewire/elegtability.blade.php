<div class="space-y-4">
    {{-- <flux:callout icon="exclamation-triangle" variant="warning" heading="Perhatian!"
        text="Jika terdapat kesalahan pada data peserta dan penerbitan SEP, Anda disarankan melakukan pendaftaran melalui Loket Pendaftaran."
        inline>
        <x-slot name="actions">
            <flux:button variant="primary" color="yellow" :href="route('new-patient')" wire:navigate>
                Antrean Loket
            </flux:button>
        </x-slot>
    </flux:callout> --}}

    {{-- DATA PESERTA --}}
    <x-participant-data :$participantData :$mrNumber :$phoneNumber />

    {{-- DATA SEP --}}
    <div class=" bg-white rounded-lg overflow-hidden">
        <div class="bg-primary-700 text-secondary-300 text-center py-1 px-4 font-bold tracking-widest">
            DATA SEP
        </div>
        <div class="p-4">
            <div class="[&>*:not(:last-child)]:mr-4 space-y-2 [&>*]:px-4 [&>*]:inline-block">
                <div>
                    <div class="font-semibold text-sm">No. Rujukan</div>
                    <div>{{ $refNumber == '' ? '-' : $refNumber }}</div>
                </div>
                <div>
                    <div class="font-semibold text-sm">Tgl Rujukan</div>
                    <div>{{ $refDate ? \Carbon\Carbon::parse($refDate)->translatedFormat('d F Y') : '-' }}</div>
                </div>
                <div>
                    <div class="font-semibold text-sm">Asal Rujukan</div>
                    <div>{{ $refOriginVisit == 2 ? 'Faskes 2 (RS)' : 'Faskes 1' }}</div>
                </div>
                <div>
                    <div class="font-semibold text-sm">PPK Rujukan</div>
                    <div>{{ $refOriginId == '' ? '-' : $refOriginId . ' - ' . $refOrigin }}</div>
                </div>
                <div>
                    <div class="font-semibold text-sm">Diagnosa</div>
                    <div>{{ $diagnoseId == '' ? '-' : $diagnoseId . ' - ' . $diagnose }}</div>
                </div>
                <div>
                    <div class="font-semibold text-sm">No. SKDP</div>
                    <div>{{ $controlNumber == '' ? '-' : $controlNumber }}</div>
                </div>
                <div>
                    <div class="font-semibold text-sm">Tgl SEP</div>
                    <div>{{ \Carbon\Carbon::parse($sepDate)->translatedFormat('d F Y') }}</div>
                </div>
                <div>
                    <div class="font-semibold text-sm">DPJP Layanan</div>
                    <div>{{ $doctorName }}</div>
                </div>
                <div>
                    <div class="font-semibold text-sm">Poli Tujuan</div>
                    <div>{{ $polyclinicName }}</div>
                </div>
                <div>
                    <div class="font-semibold text-sm">PPK Pelayanan</div>
                    <div>{{ $servicePPK }}</div>
                </div>
                <div>
                    <div class="font-semibold text-sm">Jenis Pelayanan</div>
                    <div>{{ $serviceType == 1 ? 'Ranap' : 'Ralan' }}</div>
                </div>
                <div>
                    <div class="font-semibold text-sm">Hak Kelas</div>
                    <div>{{ 'Kelas ' . $classRights }}</div>
                </div>
                <div>
                    <div class="font-semibold text-sm">Tujuan Kunjungan</div>
                    <div>{{ $this->getSepData($purposeOfVisit->name)['purposeOfVisit']['title'] }}</div>
                </div>
                <div>
                    <div class="font-semibold text-sm">Flag Prosedur</div>
                    <div>{{ $this->getSepData($purposeOfVisit->name)['flagProcedure']['title'] }}</div>
                </div>
                <div>
                    <div class="font-semibold text-sm">Penunjang</div>
                    <div>{{ $this->getSepData($purposeOfVisit->name)['support']['title'] }}</div>
                </div>
                <div>
                    <div class="font-semibold text-sm">Asesmen Pelayanan</div>
                    <div>{{ $this->getSepData($purposeOfVisit->name)['serviceAssessment']['title'] }}</div>
                </div>
                <div>
                    <div class="font-semibold text-sm">Catatan</div>
                    <div>{{ $note }}</div>
                </div>
            </div>
            <div class="[&>*:not(:last-child)]:mr-4 space-y-2 [&>*]:px-4 [&>*]:inline-block">
                <div>
                    <div class="font-semibold text-sm">Katarak</div>
                    <div>
                        <flux:radio.group :disabled="!$isOriginValid" wire:target="checkBiometricStatus,process"
                            wire:model="cataract" variant="segmented">
                            <flux:radio icon="check" label="Ya" value="1" />
                            <flux:radio icon="x" label="Tidak" value="0" />
                        </flux:radio.group>
                    </div>
                </div>
                <div>
                    <div class="font-semibold text-sm">Eksekutif</div>
                    <div>
                        <flux:radio.group :disabled="!$isOriginValid" wire:target="checkBiometricStatus,process"
                            wire:model="executive" variant="segmented">
                            <flux:radio icon="check" label="Ya" value="1" />
                            <flux:radio icon="x" label="Tidak" value="0" />
                        </flux:radio.group>
                    </div>
                </div>
                <div>
                    <div class="font-semibold text-sm">COB</div>
                    <div>
                        <flux:radio.group :disabled="!$isOriginValid" wire:target="checkBiometricStatus,process"
                            wire:model="cob" variant="segmented">
                            <flux:radio icon="check" label="Ya" value="1" />
                            <flux:radio icon="x" label="Tidak" value="0" />
                        </flux:radio.group>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <button wire:click="process" wire:loading.attr="disabled" @disabled(!$isOriginValid)
        class="w-full h-20 rounded-2xl bg-gradient-to-br from-primary-700 to-primary-500 hover:to-yellow-300 active:scale-[0.99] transition-all duration-200 shadow-xl flex items-center justify-center gap-3 disabled:opacity-60 disabled:cursor-not-allowed">
        <svg wire:loading.remove wire:target="checkBiometricStatus,process" class="size-8 text-secondary-300" fill="none"
            stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <svg wire:loading wire:target="checkBiometricStatus,process" class="size-8 text-white animate-spin"
            fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
        </svg>
        <span wire:loading.remove wire:target="checkBiometricStatus,process"
            class="text-3xl font-black text-secondary-300 uppercase tracking-widest drop-shadow">
            Proses
        </span>
        <span wire:loading wire:target="checkBiometricStatus,process"
            class="text-3xl font-black text-white uppercase tracking-widest">
            Memproses...
        </span>
    </button>
</div>
