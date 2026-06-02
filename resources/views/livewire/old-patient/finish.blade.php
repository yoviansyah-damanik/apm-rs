<div class="flex flex-col items-center justify-center gap-3 w-full" data-area="print" data-printable="antrean-loket"
    data-page-type="antrean">
    <x-queue-number type="poli" :queueNumber="$registrationData['kd_poli'] . '-' . $registrationData['no_reg']" :date="now()->format('Y-m-d')" :data="$registrationData" />
    <flux:button size="2xl" variant="primary" color="red"
        x-on:click="printArea('antrean-loket'); window.addEventListener('afterprint', () => $wire.backToHome(), { once: true })"
        class="h-24 !text-3xl w-full print:hidden">
        Cetak Antrean Poli
    </flux:button>
</div>
