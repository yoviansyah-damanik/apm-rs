{{-- Component TTS menggunakan Web Speech API dengan queue --}}
{{-- Logic ada di resources/js/voice.js --}}
<div class="hidden" x-data="voiceComponent()"
    @speak.window="speak($event.detail.text ?? $event.detail[0]?.text ?? '')"
    @speak-stop.window="stop()">
</div>
