@props(['document'])

<div {{ $attributes->merge(['class' => 'flex flex-wrap items-center gap-2 text-xs']) }}>
    @if ($document->isViewableInBrowser())
        <a
            href="{{ route('documents.view', $document) }}"
            class="inline-flex items-center rounded-md bg-slate-800/80 px-2.5 py-1 font-medium text-emerald-400 ring-1 ring-slate-700/80 transition hover:bg-slate-800 hover:text-emerald-300"
            target="_blank"
            rel="noopener"
        >
            View
        </a>
    @endif
    <a
        href="{{ route('documents.download', $document) }}"
        class="inline-flex items-center rounded-md bg-slate-800/80 px-2.5 py-1 font-medium text-slate-300 ring-1 ring-slate-700/80 transition hover:bg-slate-800 hover:text-white"
    >
        Download
    </a>
</div>
