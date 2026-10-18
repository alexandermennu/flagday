<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#0b0b0c">
    <meta name="description" content="@yield('description', config('graduation.institution').' Class of '.config('graduation.class_year').' Digital Graduation Program — Honors Ball, Baccalaureate, and Convocation.')">

    <title>@yield('title', config('graduation.institution').' Class of '.config('graduation.class_year').' | Digital Graduation Program')</title>

    @fonts
    @vite(['resources/css/app.css', 'resources/js/app.js', 'resources/js/site.js'])
</head>
<body class="flex min-h-screen flex-col bg-white font-sans text-neutral-900 antialiased">

    <a href="#main-content" class="sr-only focus:not-sr-only focus:absolute focus:z-50 focus:m-4 focus:rounded-md focus:bg-gold-400 focus:px-4 focus:py-2 focus:text-ink">
        Skip to content
    </a>

    <x-site-header />

    <main id="main-content" class="flex flex-1 flex-col">
        @yield('content')
    </main>

    @sectionMissing('no_footer')
        <x-site-footer />
    @endif

</body>
</html>
