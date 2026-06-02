{{-- <div class="w-full h-full relative max-w-[850px] mx-auto py-4"> --}}
<div class="w-full h-full relative mx-auto py-4">
    @if ($currentStep == 1)
        <livewire:old-patient.search />
    @else
        <div class="h-full flex-1">
            <div class="flex gap-3 items-stretch mb-4">
                <button wire:click="prevFormStep"
                    class="w-11 rounded-xl bg-gradient-to-br from-primary-700 to-primary-500 active:scale-95 transition-all flex items-center justify-center text-secondary-300">
                    <svg class="size-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7" />
                    </svg>
                </button>
                <div
                    class="flex-1 relative rounded-2xl bg-gradient-to-br from-primary-700 to-primary-500 overflow-hidden shadow-lg">
                    {{-- Title --}}
                    <div class="flex-1 text-center px-4 py-4">
                        <p class="font-bold uppercase tracking-[0.2em] text-secondary-300 mb-0.5">
                            Pendaftaran Poliklinik</p>
                        <h2 class="text-2xl font-black text-white drop-shadow uppercase tracking-widest leading-tight">
                            {{ $formTitle }}
                        </h2>
                    </div>

                    {{-- Subtitle sebagai separator bawah --}}
                    <div class="border-t border-primary-50/40 px-4 py-2 text-center">
                        <p class="text-base text-white">{{ $formSubtitle }}</p>
                    </div>
                </div>
            </div>

            <div class="flex-1 overflow-visible">
                @if ($formStep == 1)
                    <livewire:old-patient.patient-type :$patient :$payTypes :$participantData :$canUseBpjs
                        :$defaultBpjsPayTypes wire:key="form-step-1" lazy />
                @elseif($formStep == 2)
                    <livewire:old-patient.purpose-of-visit :$participantData wire:key="form-step-2" :$purposeOfVisits
                        lazy />
                @elseif($formStep == 3)
                    @if (
                        $payType['kd_pj'] === env('DEFAULT_BPJS_OFFLINE_PAY_TYPE') &&
                            in_array($purposeOfVisit->group(), ['RujukPertama', 'Internal']))
                        <livewire:old-patient.bpjs-offline :$participantData wire:key="form-step-3a" lazy />
                    @else
                        @if ($purposeOfVisit->group() === 'Kontrol')
                            <livewire:old-patient.control-letter :$participantData :$purposeOfVisit
                                wire:key="form-step-3b" lazy />
                        @else
                            <livewire:old-patient.references :$participantData :isInternal="$purposeOfVisit->group() === 'Internal'" wire:key="form-step-3a"
                                lazy />
                        @endif
                    @endif
                @elseif($formStep == 4)
                    <livewire:old-patient.schedule :$controlLetter :$patient :$schedules wire:key="form-step-4"
                        :$reference lazy />
                @elseif($formStep == 5)
                    <livewire:old-patient.biometric :$participantData wire:key="form-step-5" lazy />
                @elseif($formStep == 6)
                    <livewire:old-patient.elegtability :$payType :$patient :$participantData :$reference :$schedule
                        :$defaultBpjsPayType :$controlLetter :$purposeOfVisit wire:key="form-step-6" lazy />
                @elseif($formStep == 7)
                    <livewire:old-patient.confirmation :$patient :$schedule :$purposeOfVisit :$payType
                        wire:key="form-step-7" lazy />
                @elseif($formStep == 8)
                    <livewire:old-patient.finish :$registration wire:key="form-step-8" lazy />
                @endif
            </div>
        </div>
    @endif
</div>
