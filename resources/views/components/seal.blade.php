{{-- The Starz University crest, used as the site's brand mark throughout. Swap public/images/branding/starz-crest.svg for the official logo when available. --}}
@props(['class' => 'w-12 h-12'])
<img src="{{ asset('images/branding/starz-crest.svg') }}" class="{{ $class }} object-contain" alt="{{ config('graduation.institution') }} crest">
