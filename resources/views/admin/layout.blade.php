<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Dashboard') | Admin</title>

    @fonts
    @vite(['resources/css/app.css', 'resources/js/app.js', 'resources/js/admin.js'])
</head>
<body class="min-h-screen bg-slate-50 font-sans text-slate-900 antialiased">
    <div class="flex min-h-screen">
        <aside class="hidden w-64 shrink-0 border-r border-slate-200 bg-white sm:block">
            <div class="flex items-center gap-3 border-b border-slate-200 px-6 py-5">
                <x-seal class="h-9 w-9 shrink-0" />
                <span class="leading-tight">
                    <span class="block text-[10px] font-semibold uppercase tracking-wider text-red-700">Republic of Liberia</span>
                    <span class="block text-sm font-bold text-blue-950">Admin</span>
                </span>
            </div>
            @php
                $isResponses = request()->routeIs('admin.attendees.index') && request()->query('status') === 'responded';
                $isInvitees = request()->routeIs('admin.attendees.*') && ! $isResponses;
            @endphp
            <nav class="space-y-1 px-3 py-4">
                <a href="{{ route('admin.dashboard') }}"
                   class="flex items-center gap-2 rounded-lg px-3 py-2 text-sm font-medium transition {{ request()->routeIs('admin.dashboard') ? 'bg-blue-950 text-white' : 'text-slate-600 hover:bg-slate-100' }}">
                    <span aria-hidden="true">📊</span> Dashboard
                </a>
                <a href="{{ route('admin.attendees.index') }}"
                   class="flex items-center gap-2 rounded-lg px-3 py-2 text-sm font-medium transition {{ $isInvitees ? 'bg-blue-950 text-white' : 'text-slate-600 hover:bg-slate-100' }}">
                    <span aria-hidden="true">👥</span> Invitees
                </a>
                <a href="{{ route('admin.attendees.index', ['status' => 'responded']) }}"
                   class="flex items-center gap-2 rounded-lg px-3 py-2 text-sm font-medium transition {{ $isResponses ? 'bg-blue-950 text-white' : 'text-slate-600 hover:bg-slate-100' }}">
                    <span aria-hidden="true">📨</span> RSVP Responses
                </a>
                <a href="{{ route('admin.reports.index') }}"
                   class="flex items-center gap-2 rounded-lg px-3 py-2 text-sm font-medium transition {{ request()->routeIs('admin.reports.*') ? 'bg-blue-950 text-white' : 'text-slate-600 hover:bg-slate-100' }}">
                    <span aria-hidden="true">📈</span> Reports
                </a>
            </nav>
        </aside>

        <div class="flex-1">
            <header class="flex items-center justify-between border-b border-slate-200 bg-white px-6 py-4">
                <h1 class="text-lg font-bold text-blue-950">@yield('title', 'Dashboard')</h1>
                <form method="POST" action="{{ route('admin.logout') }}">
                    @csrf
                    <button type="submit" class="text-sm font-medium text-slate-500 transition hover:text-red-700">Sign out</button>
                </form>
            </header>

            <main class="p-6">
                @if (session('success'))
                    <div class="mb-6 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
                        {{ session('success') }}
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>
</body>
</html>
