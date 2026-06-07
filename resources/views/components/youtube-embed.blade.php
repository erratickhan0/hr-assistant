@props([
    'videoId',
    'title' => 'Video',
    'start' => null,
])

@php
    $watchUrl = 'https://www.youtube.com/watch?v='.urlencode($videoId);
    $thumbnailUrl = 'https://img.youtube.com/vi/'.urlencode($videoId).'/hqdefault.jpg';
    $embedQuery = array_filter([
        'autoplay' => '1',
        'rel' => '0',
        'modestbranding' => '1',
        'start' => $start,
        'origin' => rtrim((string) config('app.url'), '/'),
    ], fn ($value) => $value !== null && $value !== '');
    $embedUrl = 'https://www.youtube.com/embed/'.urlencode($videoId).'?'.http_build_query($embedQuery);
    $instanceId = 'youtube-embed-'.md5($videoId.$title);
@endphp

<div {{ $attributes->merge(['class' => 'space-y-2']) }}>
    <div class="overflow-hidden rounded-xl border border-slate-800/80 bg-slate-950 shadow-lg shadow-black/30 ring-1 ring-white/5">
        <div id="{{ $instanceId }}" class="relative aspect-video w-full bg-slate-900">
            <button
                type="button"
                class="group absolute inset-0 flex w-full items-center justify-center"
                data-youtube-play
                data-target="{{ $instanceId }}"
                data-embed-url="{{ $embedUrl }}"
                data-embed-title="{{ $title }}"
                aria-label="Play {{ $title }}"
            >
                <img
                    src="{{ $thumbnailUrl }}"
                    alt=""
                    class="absolute inset-0 size-full object-cover opacity-90 transition group-hover:opacity-100"
                    loading="lazy"
                />
                <span class="absolute inset-0 bg-slate-950/30 transition group-hover:bg-slate-950/20"></span>
                <span class="relative flex size-16 items-center justify-center rounded-full bg-emerald-500 text-slate-950 shadow-lg shadow-black/40 ring-4 ring-emerald-500/30 transition group-hover:scale-105 group-hover:bg-emerald-400">
                    <svg class="ml-1 size-7" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                        <path d="M8 5.14v13.72L19 12 8 5.14z"/>
                    </svg>
                </span>
            </button>
        </div>
    </div>
    <p class="text-center text-xs text-slate-500">
        Video not loading?
        <a href="{{ $watchUrl }}" class="link-accent" target="_blank" rel="noopener noreferrer">Watch on YouTube</a>
    </p>
</div>

@once
    @push('scripts')
        <script>
            document.addEventListener('click', function (event) {
                const button = event.target.closest('[data-youtube-play]');
                if (!button) {
                    return;
                }

                const targetId = button.getAttribute('data-target');
                const embedUrl = button.getAttribute('data-embed-url');
                const embedTitle = button.getAttribute('data-embed-title');
                const container = targetId ? document.getElementById(targetId) : null;

                if (!container || !embedUrl) {
                    return;
                }

                const iframe = document.createElement('iframe');
                iframe.src = embedUrl;
                iframe.title = embedTitle || 'YouTube video';
                iframe.className = 'absolute inset-0 size-full';
                iframe.allow = 'accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share';
                iframe.referrerPolicy = 'strict-origin-when-cross-origin';
                iframe.allowFullscreen = true;

                container.replaceChildren(iframe);
            });
        </script>
    @endpush
@endonce
