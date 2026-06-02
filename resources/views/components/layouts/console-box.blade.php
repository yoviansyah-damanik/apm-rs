<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="{{ Vite::image('logo-icon.png') }}" type="image/x-icon">

    <title>{{ $title ?? env('APP_NAME') }}</title>
    @livewireStyles
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

{{-- Ganti bg-overlay-3 dengan bg-overlay-1 s/d bg-overlay-6 untuk memilih variasi gradasi --}}

<body class="bg-overlay bg-overlay-5 flex flex-col overflow-hidden !h-dvh !w-dvw select-none"
    style="--bg-img: url('{{ Vite::image('bg.jpg') }}');" oncontextmenu="return false;">
    <x-layouts.console-box.header />

    <div class="flex-1 overflow-x-hidden overflow-y-auto my-4">
        <main class="flex flex-col h-full mx-auto max-w-7xl px-4">
            {{ $slot }}
        </main>
    </div>

    <x-layouts.console-box.footer />
    <x-voice />
    @livewireScripts
    @fluxScripts
    @stack('scripts')
</body>

</html>
