<div class="w-full h-full relative mx-auto">
    @if ($currentStep === 1)
        <livewire:participant-checker.search />
    @elseif($currentStep === 2)
        <livewire:participant-checker.finish :$participantData lazy />
    @endif
</div>
