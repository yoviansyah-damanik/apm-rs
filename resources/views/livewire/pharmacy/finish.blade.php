<div class="space-y-4">
    <div class="py-4 mb-4 space-y-2 text-center bg-primary-700 rounded-xl relative">
        <flux:button variant="ghost" wire:click="$dispatch('setStep',{step: 1})"
            class="w-20 h-full !absolute top-0 bottom-0 left-0 !m-0 !text-secondary-300" icon="chevron-left" inset>
        </flux:button>
        <div class="flex-1">
            <div class="text-secondary-300 font-bold text-3xl uppercase tracking-widest">
                Cetak Antrean Farmasi
            </div>
            <flux:text class="text-white">
                Silahkan lakukan cetak antrean farmasi Anda melalui tombol di bawah.
            </flux:text>
        </div>
    </div>

    <x-queue-number
        type="farmasi"
        :recipes="$registerData['recipe'] ?? []"
        :data="$data"
        data-area="print"
        data-printable="antrean-farmasi"
        data-page-type="antrean"
    />

    <flux:button size="2xl" variant="primary" color="red"
        x-on:click="printArea('antrean-farmasi'); window.addEventListener('afterprint', () => $wire.backToHome(), { once: true })"
        class="h-24 !text-3xl w-full print:hidden">
        Ambil Antrean Farmasi
    </flux:button>
</div>
