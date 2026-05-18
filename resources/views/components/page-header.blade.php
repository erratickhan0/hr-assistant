@props([
    'eyebrow' => null,
    'title',
    'lead' => null,
])

<header {{ $attributes->merge(['class' => 'space-y-3']) }}>
    @if ($eyebrow)
        <p class="page-eyebrow">{{ $eyebrow }}</p>
    @endif
    <h1 class="page-title">{{ $title }}</h1>
    @if ($lead)
        <p class="page-lead max-w-2xl">{{ $lead }}</p>
    @endif
</header>
