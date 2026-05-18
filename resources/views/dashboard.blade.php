@extends('layouts.app')

@section('title', 'Dashboard — '.config('app.name'))

@section('content')
    <div class="space-y-8">
        <div class="flex flex-col gap-6 lg:flex-row lg:items-start lg:justify-between">
            <x-page-header
                :title="$organization->name"
                :lead="'Signed in as '.auth()->user()->email"
            />
            <div class="app-card-sm lg:min-w-[18rem] lg:shrink-0">
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Public portal</p>
                <a
                    href="{{ route('agency.portal', $organization) }}"
                    class="link-accent mt-2 inline-block break-all text-sm"
                    target="_blank"
                    rel="noopener"
                >
                    {{ url('/agency/'.$organization->slug) }}
                </a>
            </div>
        </div>

        @if (session('registered_slug'))
            <div class="alert-success">
                Save your agency URL name for next time:
                <code class="ml-1 rounded-md bg-slate-900/80 px-2 py-0.5 font-mono text-emerald-300">{{ session('registered_slug') }}</code>
            </div>
        @endif

        <section class="app-card">
            <h2 class="section-title">Search CVs</h2>
            <p class="mt-2 text-sm leading-relaxed text-slate-400">
                Describe the skills or experience you need. We surface the most relevant CVs and highlight matching phrases where we find them.
            </p>
            <form
                id="cv-search-form"
                method="POST"
                action="{{ route('hr.search') }}"
                class="mt-6 flex flex-col gap-3 sm:flex-row sm:items-end"
            >
                @csrf
                <div class="flex-1">
                    <label for="q" class="form-label">What are you looking for?</label>
                    <input
                        id="q"
                        name="q"
                        type="text"
                        value="{{ $searchQuery ?? '' }}"
                        required
                        minlength="2"
                        maxlength="2000"
                        placeholder="e.g. experienced carpenter with site supervision"
                        class="form-input"
                    />
                </div>
                <button type="submit" id="cv-search-submit" class="btn-primary sm:shrink-0">
                    <span id="cv-search-submit-text">Search</span>
                    <span id="cv-search-submit-loading" class="hidden items-center gap-2">
                        <svg class="size-4 animate-spin" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <circle class="opacity-30" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3"></circle>
                            <path class="opacity-100" d="M22 12a10 10 0 0 0-10-10" stroke="currentColor" stroke-width="3" stroke-linecap="round"></path>
                        </svg>
                        <span>Searching...</span>
                    </span>
                </button>
                <button type="submit" formaction="{{ route('hr.search.clear') }}" class="btn-secondary sm:shrink-0">
                    Clear
                </button>
            </form>

            @if ($searchAnswer)
                <div class="mt-8 rounded-xl border border-emerald-900/40 bg-emerald-950/25 p-5">
                    <h3 class="text-xs font-semibold uppercase tracking-wide text-emerald-400/90">Summary</h3>
                    <div class="mt-3 whitespace-pre-wrap text-sm leading-relaxed text-slate-200">{{ $searchAnswer }}</div>
                </div>
            @endif

            @if ($searchResults !== null && $searchResults->isNotEmpty())
                <div class="mt-8 border-t border-slate-800/80 pt-6">
                    <h3 class="text-sm font-semibold text-slate-300">Top matches</h3>
                    <ul class="mt-4 space-y-3">
                        @foreach ($searchResults as $doc)
                            <li class="rounded-xl border border-slate-800/70 bg-slate-950/50 p-4">
                                @php
                                    $docEvidence = $searchEvidence[$doc->id] ?? null;
                                @endphp
                                <p class="font-medium text-slate-100">
                                    {{ $doc->candidate?->display_name ?: 'Candidate' }}
                                    <span class="font-normal text-slate-500">· {{ $doc->original_name }}</span>
                                </p>
                                @if (is_array($docEvidence))
                                    <p class="mt-1.5 text-xs text-slate-400">
                                        {{ $docEvidence['match_count'] ?? 0 }} matching terms{{ ! empty($docEvidence['phrase_count']) ? ' · exact phrase found' : '' }}
                                    </p>
                                    @if (! empty($docEvidence['matched_terms']) && is_array($docEvidence['matched_terms']))
                                        <div class="mt-2 flex flex-wrap gap-1.5">
                                            @foreach ($docEvidence['matched_terms'] as $term)
                                                <span class="rounded-md bg-slate-800/80 px-2 py-0.5 text-[11px] text-slate-300">{{ $term }}</span>
                                            @endforeach
                                        </div>
                                    @endif
                                    @if (! empty($docEvidence['snippet']) && is_string($docEvidence['snippet']))
                                        <p class="mt-2 text-xs leading-relaxed text-slate-300">
                                            “{{ $docEvidence['snippet'] }}”
                                        </p>
                                    @endif
                                @else
                                    <p class="mt-1.5 text-xs text-slate-500">Included based on overall relevance to your search.</p>
                                @endif
                                <x-document-actions :document="$doc" class="mt-3 justify-start" />
                            </li>
                        @endforeach
                    </ul>
                </div>
            @elseif ($searchResults !== null && $searchResults->isEmpty())
                <p class="mt-6 rounded-lg border border-dashed border-slate-700/80 bg-slate-950/30 px-4 py-6 text-center text-sm text-slate-500">
                    No matches for that query. Try different keywords or a broader description.
                </p>
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
