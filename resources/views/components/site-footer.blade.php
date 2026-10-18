{{-- Shared public site footer. --}}
<footer class="border-t-2 border-gold-500 bg-ink text-neutral-300" style="padding-bottom: env(safe-area-inset-bottom, 0px)">
    <div class="mx-auto max-w-7xl px-5 py-12 lg:px-8">
        <div class="grid gap-10 md:grid-cols-3">
            <div>
                <a href="{{ route('landing') }}" class="flex items-center gap-3">
                    <x-seal class="h-16 w-16 shrink-0" />
                    <span class="leading-tight">
                        <span class="block font-display text-lg font-semibold text-white">{{ config('graduation.institution') }}</span>
                        <span class="block text-[11px] font-semibold uppercase tracking-[0.2em] text-gold-400">Class of {{ config('graduation.class_year') }}</span>
                    </span>
                </a>
                <p class="mt-4 max-w-xs text-sm leading-relaxed text-neutral-400">
                    The official Digital Graduation Program for the {{ config('graduation.institution') }} Class of {{ config('graduation.class_year') }}.
                </p>
            </div>

            <nav aria-label="Footer">
                <h2 class="text-xs font-semibold uppercase tracking-[0.2em] text-gold-400">Explore</h2>
                <ul class="mt-4 space-y-2.5">
                    <li><a href="{{ route('landing') }}" class="text-sm transition hover:text-gold-300">Home</a></li>
                    <li><a href="{{ route('class.show') }}" class="text-sm transition hover:text-gold-300">Class of {{ config('graduation.class_year') }}</a></li>
                    <li><a href="{{ route('events.show', 'honors-ball') }}" class="text-sm transition hover:text-gold-300">Honors Ball</a></li>
                    <li><a href="{{ route('rsvp.create') }}" class="text-sm transition hover:text-gold-300">RSVP — Honors Ball</a></li>
                </ul>
            </nav>

            <div>
                <h2 class="text-xs font-semibold uppercase tracking-[0.2em] text-gold-400">Contact</h2>
                <ul class="mt-4 space-y-2.5 text-sm">
                    <li>{{ config('graduation.contact_email') ?: 'Contact email to be announced' }}</li>
                    <li>{{ config('graduation.contact_phone') ?: 'Contact phone to be announced' }}</li>
                </ul>
            </div>
        </div>

        <div class="mt-10 flex flex-col items-center justify-between gap-3 border-t border-white/10 pt-6 text-xs text-neutral-500 sm:flex-row">
            <p>&copy; {{ date('Y') }} {{ config('graduation.institution') }}. All rights reserved.</p>
            <a href="{{ route('admin.login') }}" class="transition hover:text-neutral-300">Administrator sign in</a>
        </div>
    </div>
</footer>
