<div class="w-full h-full relative mx-auto py-4 px-4">
    @if ($currentStep == 1)
        <livewire:check-in.search />
    @else
        <div class="h-full overflow-hidden flex flex-col">
            <div class="flex-1 overflow-auto h-full">
                <div class="py-4 h-full flex flex-col">
                    <div class="py-4 mb-4 space-y-2 text-center bg-primary-700 rounded-xl relative">
                        <flux:button variant="ghost" wire:click="prevFormStep"
                            class="w-20 h-full !absolute top-0 bottom-0 left-0 !m-0 !text-secondary-300" icon="chevron-left"
                            inset>
                        </flux:button>
                        <div class="flex-1">
                            <div class="text-secondary-300 font-bold text-3xl uppercase tracking-widest">
                                {{ $formTitle }}
                            </div>
                            <flux:text class="text-white">
                                {{ $formSubtitle }}
                            </flux:text>
                        </div>
                    </div>

                    <div class="flex-1 overflow-auto">
                        @if ($formStep == 1)
                            <livewire:check-in.biometric :$participantData wire:key="form-step-1" />
                        @elseif($formStep == 2)
                            <livewire:check-in.elegtability :$patient :$participantData :$defaultBpjsPayType
                                :$jknBooking wire:key="form-step-2" />
                        @elseif($formStep == 3)
                            <livewire:check-in.finish :$jknBooking wire:key="form-step-3" lazy />
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
