{{-- The official Starz University logo, used as the site's brand mark throughout. The file is square, so give it equal height/width classes; object-contain keeps its proportions regardless. --}}
@props(['class' => 'w-12 h-12'])
<img src="{{ asset('images/starz-logo.png') }}" class="{{ $class }} object-contain" alt="Starz University logo">
