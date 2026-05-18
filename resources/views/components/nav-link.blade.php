@props(['href', 'active' => false])

<a
    href="{{ $href }}"
    {{ $attributes->class([
        'nav-link',
        'nav-link-active' => $active,
    ]) }}
>
    {{ $slot }}
</a>
