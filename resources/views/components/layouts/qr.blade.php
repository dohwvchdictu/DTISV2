<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
    <title>{{ $title ?? 'Receive Document | DTIS' }}</title>
    <link rel="icon" href="{!! asset('/img/doh.ico') !!}" />
    @livewireStyles
    @vite('resources/css/app.css')
    @vite('resources/js/app.js')
</head>
<body class="min-h-screen bg-gray-100">

    {{ $slot }}

    @livewireScripts
    <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <x-livewire-alert::scripts />

</body>
</html>
