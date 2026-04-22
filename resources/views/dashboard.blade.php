@extends('layouts.app')

@section('title', 'Dashboard — '.config('app.name'))

@section('content')
    <div class="space-y-8">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-white">{{ $organization->name }}</h1>
                <p class="mt-1 text-sm text-slate-400">
                    Signed in as <span class="text-slate-200">{{ auth()->user()->email }}</span>
                </p>
            </div>
            <div class="rounded-lg border border-slate-800 bg-slate-900/60 px-4 py-3 text-sm">
                <p class="font-medium text-slate-300">Public portal</p>
                <a
                    href="{{ route('agency.portal', $organization) }}"
                    class="mt-1 inline-block break-all text-emerald-400 hover:text-emerald-300"
                    target="_blank"
                    rel="noopener"
                >
                    {{ url('/agency/'.$organization->slug) }}
                </a>
            </div>
        </div>

        @if (session('registered_slug'))
            <div class="rounded-lg border border-emerald-800/50 bg-emerald-950/30 px-4 py-3 text-sm text-emerald-100">
                Save your agency slug:
                <code class="ml-1 rounded bg-slate-900 px-1.5 py-0.5 text-emerald-300">{{ session('registered_slug') }}</code>
                — you need it to log in next time.
            </div>
        @endif

        <section class="rounded-lg border border-slate-800 bg-slate-900/40 p-5">
            <h2 class="text-lg font-medium text-white">Search CVs</h2>
            <p class="mt-1 text-sm text-slate-400">
                Uses <strong class="text-slate-200">semantic search</strong> to retrieve relevant CVs, then shows
                <strong class="text-slate-200">keyword evidence</strong> (which file contains your words).
            </p>
            <form
                id="cv-search-form"
                method="POST"
                action="{{ route('hr.search') }}"
                class="mt-4 flex flex-col gap-3 sm:flex-row sm:items-end"
            >
                @csrf
                <div class="flex-1">
                    <label for="q" class="mb-1 block text-xs font-medium uppercase tracking-wide text-slate-500">Question</label>
                    <input
                        id="q"
                        name="q"
                        type="text"
                        value="{{ $searchQuery ?? '' }}"
                        required
                        minlength="2"
                        maxlength="2000"
                        placeholder='e.g. "Laravel and AWS experience"'
                        class="w-full rounded-md border border-slate-700 bg-slate-950 px-3 py-2 text-sm text-white placeholder:text-slate-600 focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500"
                    />
                </div>
                <button
                    type="submit"
                    id="cv-search-submit"
                    class="rounded-md bg-emerald-500 px-4 py-2 text-sm font-semibold text-slate-950 hover:bg-emerald-400 sm:shrink-0"
                >
                    <span id="cv-search-submit-text">Search</span>
                    <span
                        id="cv-search-submit-loading"
                        class="hidden items-center gap-2"
                    >
                        <svg class="size-4 animate-spin text-slate-950" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <circle class="opacity-30" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3"></circle>
                            <path class="opacity-100" d="M22 12a10 10 0 0 0-10-10" stroke="currentColor" stroke-width="3" stroke-linecap="round"></path>
                        </svg>
                        <span>Searching...</span>
                    </span>
                </button>
                <button
                    type="submit"
                    formaction="{{ route('hr.search.clear') }}"
                    class="rounded-md border border-slate-700 px-4 py-2 text-sm font-medium text-slate-200 hover:bg-slate-800 sm:shrink-0"
                >
                    Clear search
                </button>
            </form>

            @if ($searchAnswer)
                <div class="mt-6 rounded-lg border border-emerald-900/50 bg-emerald-950/20 p-4">
                    <h3 class="text-xs font-medium uppercase tracking-wide text-emerald-500/90">AI answer (from matched CV text)</h3>
                    <div class="mt-2 whitespace-pre-wrap text-sm leading-relaxed text-slate-200">{{ $searchAnswer }}</div>
                </div>
            @endif

            @if ($searchResults !== null && $searchResults->isNotEmpty())
                <div class="mt-6 border-t border-slate-800 pt-5">
                    <h3 class="text-sm font-medium text-slate-300">Top matches</h3>
                    <ul class="mt-3 space-y-3">
                        @foreach ($searchResults as $doc)
                            <li class="rounded-md border border-slate-800 bg-slate-950/60 px-3 py-3 text-sm">
                                @php
                                    $docEvidence = $searchEvidence[$doc->id] ?? null;
                                @endphp
                                <p class="font-medium text-slate-100">
                                    {{ $doc->candidate?->display_name ?: 'Candidate' }}
                                    <span class="text-slate-500">· {{ $doc->original_name }}</span>
                                </p>
                                @if (is_array($docEvidence))
                                    <p class="mt-1 text-xs text-slate-400">
                                        {{ $docEvidence['match_count'] ?? 0 }} keyword hits{{ ! empty($docEvidence['phrase_count']) ? ' · phrase matched' : '' }}
                                    </p>
                                    @if (! empty($docEvidence['matched_terms']) && is_array($docEvidence['matched_terms']))
                                        <div class="mt-2 flex flex-wrap gap-1">
                                            @foreach ($docEvidence['matched_terms'] as $term)
                                                <span class="rounded bg-slate-800 px-1.5 py-0.5 text-[11px] text-slate-300">{{ $term }}</span>
                                            @endforeach
                                        </div>
                                    @endif
                                    @if (! empty($docEvidence['snippet']) && is_string($docEvidence['snippet']))
                                        <p class="mt-2 text-xs text-slate-300">
                                            “{{ $docEvidence['snippet'] }}”
                                        </p>
                                    @endif
                                @else
                                    <p class="mt-1 text-xs text-slate-500">No direct keyword hit in extracted text, shown by semantic similarity.</p>
                                @endif
                                <a
                                    href="{{ route('documents.download', $doc) }}"
                                    class="mt-2 inline-block text-xs text-emerald-400 hover:text-emerald-300"
                                >
                                    View / download CV
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @elseif ($searchResults !== null && $searchResults->isEmpty())
                <p class="mt-4 text-sm text-slate-500">No matches for that query.</p>
            @endif
        </section>
    </div>

    <script>
        (function () {
            const form = document.getElementById('cv-search-form');
            const submitButton = document.getElementById('cv-search-submit');
            const submitText = document.getElementById('cv-search-submit-text');
            const loadingState = document.getElementById('cv-search-submit-loading');

            if (!form || !submitButton || !submitText || !loadingState) {
                return;
            }

            form.addEventListener('submit', function (event) {
                const target = event.submitter;
                if (target && target.getAttribute('formaction') && target.getAttribute('formaction').includes('/search/clear')) {
                    return;
                }

                submitButton.setAttribute('disabled', 'disabled');
                submitButton.classList.add('opacity-70', 'cursor-not-allowed');
                submitText.classList.add('hidden');
                loadingState.classList.remove('hidden');
                loadingState.classList.add('inline-flex');
            });
        })();
    </script>
@endsection
