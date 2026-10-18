<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin Login | {{ config('event.name') }}</title>

    @fonts
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex min-h-screen items-center justify-center bg-slate-100 font-sans text-slate-900 antialiased">

    <div class="w-full max-w-sm px-6">
        <div class="mb-8 flex flex-col items-center text-center">
            <x-seal class="h-20 w-20" />
            <p class="mt-4 text-[11px] font-semibold uppercase tracking-wider text-gold-600">{{ config('graduation.institution') }}</p>
            <h1 class="text-lg font-bold text-ink">Admin Dashboard</h1>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-8 shadow-sm">
            @if ($errors->any())
                <div class="mb-6 rounded-lg border border-red-200 bg-red-50 p-3 text-sm text-red-700">
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('admin.login.store') }}" class="space-y-5">
                @csrf

                <div>
                    <label for="email" class="mb-1.5 block text-sm font-semibold text-slate-700">Email</label>
                    <input type="email" name="email" id="email" required autofocus value="{{ old('email') }}"
                           class="w-full rounded-lg border border-slate-300 px-4 py-2.5 text-sm focus:border-gold-500 focus:outline-none focus:ring-1 focus:ring-gold-500">
                </div>

                <div>
                    <label for="password" class="mb-1.5 block text-sm font-semibold text-slate-700">Password</label>
                    <input type="password" name="password" id="password" required
                           class="w-full rounded-lg border border-slate-300 px-4 py-2.5 text-sm focus:border-gold-500 focus:outline-none focus:ring-1 focus:ring-gold-500">
                </div>

                <label class="flex items-center gap-2 text-sm text-slate-600">
                    <input type="checkbox" name="remember" class="rounded accent-gold-500">
                    Keep me signed in
                </label>

                <button type="submit"
                        class="w-full rounded-md bg-ink px-6 py-3 text-sm font-semibold text-gold-300 shadow-sm transition hover:bg-neutral-800 active:scale-[0.98]">
                    Sign In
                </button>
            </form>
        </div>

        <p class="mt-6 text-center text-xs text-slate-400">
            <a href="{{ route('landing') }}" class="hover:text-slate-600">&larr; Back to graduation site</a>
        </p>
    </div>

</body>
</html>
