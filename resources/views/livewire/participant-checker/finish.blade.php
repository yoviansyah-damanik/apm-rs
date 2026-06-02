<div class="space-y-4">
    <x-participant-data :$participantData />
    <x-reference-data :$listOfReferences />
    <x-control-letter-data :$listOfControlLetters />

    <flux:button size="2xl" variant="primary" wire:click="backToHome" class="h-24 !text-3xl w-full print:hidden">
        Kembali ke Awal
    </flux:button>
</div>
