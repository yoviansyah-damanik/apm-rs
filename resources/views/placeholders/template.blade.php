<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="{{ Vite::image('logo-icon.png') }}" type="image/x-icon">

    <title>Placeholder Tester</title>
    @livewireStyles
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-primary-700">
    <div class="container mx-auto">
        @include('placeholders.' . $placeholderName)
    </div>
    @livewireScripts
    @fluxScripts
    @stack('scripts')
</body>

</html>