@props(['class' => 'w-32 h-auto'])
@php $stripeHeight = 100 / 11; @endphp
<svg viewBox="0 0 190 100" class="{{ $class }}" xmlns="http://www.w3.org/2000/svg" role="img" aria-label="Flag of the Republic of Liberia">
    <rect width="190" height="100" fill="#ffffff" />
    @for ($i = 0; $i < 11; $i += 2)
        <rect x="0" y="{{ $i * $stripeHeight }}" width="190" height="{{ $stripeHeight }}" fill="#BF0A30" />
    @endfor
    <rect x="0" y="0" width="76" height="{{ $stripeHeight * 6 }}" fill="#002868" />
    <polygon fill="#ffffff" points="38.00,11.27 41.64,22.25 53.22,22.33 43.90,29.19 47.40,40.21 38.00,33.47 28.60,40.21 32.10,29.19 22.78,22.33 34.35,22.25" />
</svg>
