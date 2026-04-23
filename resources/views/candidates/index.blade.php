@extends('layouts.app')

@section('title', 'Candidates — '.config('app.name'))

@section('content')
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-white">Candidates</h1>
                <p class="mt-1 text-sm text-slate-400">{{ $organization->name }}</p>
            </div>
        </div>

        <section class="rounded-lg border border-slate-800 bg-slate-900/40 p-5">
            <form
                id="candidates-search-form"
                method="GET"
                action="{{ route('candidates.index') }}"
                class="flex flex-col gap-3 sm:flex-row sm:items-end"
            >
                <div class="flex-1">
                    <label for="q" class="mb-1 block text-xs font-medium uppercase tracking-wide text-slate-500">Search by name, email, or resume name</label>
                    <input
                        id="q"
                        name="q"
                        type="text"
                        value="{{ $search ?? '' }}"
                        placeholder='e.g. "carpenter", "jamie@example.test", or "resume"'
                        class="w-full rounded-md border border-slate-700 bg-slate-950 px-3 py-2 text-sm text-white placeholder:text-slate-600 focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500"
                    />
                </div>
                <button
                    type="submit"
                    id="candidates-search-submit"
                    class="rounded-md bg-emerald-500 px-4 py-2 text-sm font-semibold text-slate-950 hover:bg-emerald-400"
                >
                    <span id="candidates-search-submit-text">Search</span>
                    <span id="candidates-search-submit-loading" class="hidden items-center gap-2">
                        <svg class="size-4 animate-spin text-slate-950" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <circle class="opacity-30" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3"></circle>
                            <path class="opacity-100" d="M22 12a10 10 0 0 0-10-10" stroke="currentColor" stroke-width="3" stroke-linecap="round"></path>
                        </svg>
                        <span>Searching...</span>
                    </span>
                </button>
                @if ($search)
                    <a
                        href="{{ route('candidates.index') }}"
                        class="rounded-md border border-slate-700 px-4 py-2 text-sm font-medium text-slate-200 hover:bg-slate-800"
                    >
                        Clear
                    </a>
                @endif
            </form>
        </section>

        <section>
            @if ($candidates->isEmpty())
                <p class="text-sm text-slate-500">No candidates found.</p>
            @else
                <ul class="divide-y divide-slate-800 rounded-lg border border-slate-800">
                    @foreach ($candidates as $candidate)
                        <li class="flex flex-col gap-2 px-4 py-4 sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <p class="font-medium text-slate-100">
                                    {{ $candidate->display_name ?: 'Unnamed candidate' }}
                                </p>
                                <p class="text-xs text-slate-500">
                                    {{ $candidate->email ?: '—' }} · {{ $candidate->created_at->diffForHumans() }}
                                </p>
                            </div>
                            <div class="text-right text-xs text-slate-400">
                                @if ($candidate->documents->isNotEmpty())
                                    @php
                                        $doc = $candidate->documents->first();
                                    @endphp
                                    <span class="block">{{ $doc->original_name }}</span>
                                    <span class="mt-0.5 inline-block rounded bg-slate-800 px-2 py-0.5 text-[11px] uppercase tracking-wide text-slate-300">
                                        {{ $doc->processing_status->value }}
                                    </span>
                                    <div class="mt-2 flex items-center justify-end gap-3">
                                        <a
                                            href="{{ route('documents.view', $doc) }}"
                                            class="text-[11px] text-emerald-400 hover:text-emerald-300"
                                            target="_blank"
                                            rel="noopener"
                                        >
                                            View
                                        </a>
                                        <a
                                            href="{{ route('documents.download', $doc) }}"
                                            class="text-[11px] text-emerald-400 hover:text-emerald-300"
                                        >
                                            Download
                                        </a>
                                    </div>
                                @else
                                    <span>No file</span>
                                @endif
                            </div>
                        </li>
                    @endforeach
                </ul>
                <div class="mt-4">
                    {{ $candidates->links() }}
                </div>
            @endif
        </section>
    </div>

    <script>
        (function () {
            const form = document.getElementById('candidates-search-form');
            const submitButton = document.getElementById('candidates-search-submit');
            const submitText = document.getElementById('candidates-search-submit-text');
            const loadingState = document.getElementById('candidates-search-submit-loading');

            if (!form || !submitButton || !submitText || !loadingState) {
                return;
            }

            form.addEventListener('submit', function () {
                submitButton.setAttribute('disabled', 'disabled');
                submitButton.classList.add('opacity-70', 'cursor-not-allowed');
                submitText.classList.add('hidden');
                loadingState.classList.remove('hidden');
                loadingState.classList.add('inline-flex');
            });
        })();
    </script>
@endsection
