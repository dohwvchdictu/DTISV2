<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap"
        rel="stylesheet">

    @livewireStyles
    
    @vite('resources/css/app.css')
    @vite('resources/js/app.js')

    <title>{{ $title ?? 'Page Title' }}</title>
    <link rel="icon" href="{!! asset('/img/doh.ico') !!}"/>

</head>

<body class="bg-slate-100 dark:bg-slate-700">

    <main>
        @livewire('header-section')
        @livewire('partials.navbar')
        @livewire('partials.sidebar')
        {{-- Spacer for the fixed banner (90px) + navbar (55px) --}}
        <div style="height: 145px"></div>
        {{ $slot }}
    </main>

    @livewireScripts

    {{-- Livewire Alert --}}
    <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <x-livewire-alert::scripts />

</body>

</html>