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

    <style>
        /* Dark mode toggle icon swap (sun shown in dark, moon in light) */
        html.dark .icon-sun { display: block; }
        html.dark .icon-moon { display: none; }

        @media (min-width: 1024px) {
            body.sidebar-collapsed #hs-application-sidebar { width: 64px; }
            body.sidebar-collapsed .lg\:ps-64 { padding-inline-start: 64px; }
            body.sidebar-collapsed #hs-application-sidebar .hs-accordion-content { display: none; }
            body.sidebar-collapsed #hs-application-sidebar nav a,
            body.sidebar-collapsed #hs-application-sidebar nav button,
            body.sidebar-collapsed #sidebar-collapse-toggle {
                font-size: 0;
                gap: 0;
                justify-content: center;
                padding-inline: 8px;
            }
            body.sidebar-collapsed #hs-application-sidebar nav a > span.inline-flex { display: none; }
            body.sidebar-collapsed #hs-application-sidebar nav .ms-auto { display: none; }
            body.sidebar-collapsed #sidebar-collapse-toggle svg { transform: rotate(180deg); }
        }
    </style>
    <script>
        if (localStorage.getItem('darkMode') === '1') document.documentElement.classList.add('dark');
        function toggleDarkMode() {
            const on = document.documentElement.classList.toggle('dark');
            localStorage.setItem('darkMode', on ? '1' : '0');
        }
        function applySidebarState() {
            document.body.classList.toggle('sidebar-collapsed', localStorage.getItem('sidebarCollapsed') === '1');
        }
        function toggleSidebar() {
            localStorage.setItem('sidebarCollapsed', localStorage.getItem('sidebarCollapsed') === '1' ? '0' : '1');
            applySidebarState();
        }
        document.addEventListener('DOMContentLoaded', applySidebarState);
        document.addEventListener('livewire:navigated', applySidebarState);

        /* Keep the sidebar's scroll position across wire:navigate page swaps */
        let sidebarScrollTop = 0;
        document.addEventListener('livewire:navigating', function () {
            const nav = document.getElementById('sidebar-scroll');
            if (nav) sidebarScrollTop = nav.scrollTop;
        });
        document.addEventListener('livewire:navigated', function () {
            const nav = document.getElementById('sidebar-scroll');
            if (nav) nav.scrollTop = sidebarScrollTop;
        });
    </script>
</head>

<body class="bg-slate-100 dark:bg-neutral-900">

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