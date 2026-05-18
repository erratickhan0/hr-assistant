@extends('layouts.app')

@section('title', 'Candidates — '.config('app.name'))

@section('content')
    <div class="space-y-8">
        <x-page-header
            title="Candidates"
            :lead="$organization->name"
        />

        <section class="app-card">
            <form
                id="candidates-search-form"
                method="GET"
                action="{{ route('candidates.index') }}"
                class="flex flex-col gap-3 sm:flex-row sm:items-end"
            >
                <div class="flex-1">
                    <label for="q" class="form-label">Search by name, email, or file name</label>
                    <input
                        id="q"
                        name="q"
                        type="text"
                        value="{{ $search ?? '' }}"
                        placeholder="e.g. carpenter, jamie@example.com, resume"
                        class="form-input"
                    />
                </div>
                <button type="submit" id="candidates-search-submit" class="btn-primary">
                    <span id="candidates-search-submit-text">Search</span>
                    <span id="candidates-search-submit-loading" class="hidden items-center gap-2">
                        <svg class="size-4 animate-spin" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <circle class="opacity-30" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3"></circle>
                            <path class="opacity-100" d="M22 12a10 10 0 0 0-10-10" stroke="currentColor" stroke-width="3" stroke-linecap="round"></path>
                        </svg>
                        <span>Searching...</span>
                    </span>
                </button>
                @if ($search)
                    <a href="{{ route('candidates.index') }}" class="btn-secondary">Clear</a>
                @endif
            </form>
        </section>

        <section>
            @if ($candidates->isEmpty())
                <div class="rounded-xl border border-dashed border-slate-700/80 bg-slate-900/20 px-6 py-12 text-center">
                    <p class="text-sm text-slate-400">No candidates found.</p>
                    <p class="mt-1 text-xs text-slate-500">Share your portal link to start receiving CVs.</p>
                </div>
            @else
                <ul class="list-panel">
                    @foreach ($candidates as $candidate)
                        <li class="list-panel-row">
                            <div>
                                <p class="font-medium text-slate-100">
                                    {{ $candidate->display_name ?: 'Unnamed candidate' }}
                                </p>
                                <p class="mt-1 text-xs text-slate-500">
                                    {{ $candidate->email ?: 'No email' }} · {{ $candidate->created_at->diffForHumans() }}
                                </p>
                            </div>
                            <div class="text-left sm:text-right">
                                @if ($candidate->documents->isNotEmpty())
                                    @php
                                        $doc = $candidate->documents->first();
                                    @endphp
                                    <p class="text-xs text-slate-400">{{ $doc->original_name }}</p>
                                    <x-status-badge :status="$doc->processing_status" class="mt-2" />
                                    <x-document-actions :document="$doc" class="mt-2 justify-start sm:justify-end" />
                                @else
                                    <span class="text-xs text-slate-500">No file attached</span>
                                @endif
                            </div>
                        </li>
                    @endforeach
                </ul>
                <div class="mt-6">
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
