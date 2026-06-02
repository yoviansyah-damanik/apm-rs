<div class="w-full h-full relative mx-auto">
    @if ($currentStep === 1)
        <livewire:pharmacy.search />
    @elseif($currentStep === 2)
        <livewire:pharmacy.finish :$registerData lazy />
    @endif
</div>
