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
                Uses <strong class="text-slate-200">Pinecone + OpenAI embeddings</strong> when configured; otherwise a simple
                <strong class="text-slate-200">filename keyword</strong> match (CV text is stored in object storage, not SQL).
            </p>
            <form method="POST" action="{{ route('hr.search') }}" class="mt-4 flex flex-col gap-3 sm:flex-row sm:items-end">
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
                    class="rounded-md bg-emerald-500 px-4 py-2 text-sm font-semibold text-slate-950 hover:bg-emerald-400 sm:shrink-0"
                >
                    Search
                </button>
            </form>

            @if ($searchResults !== null && $searchResults->isNotEmpty())
                <div class="mt-6 border-t border-slate-800 pt-5">
                    <h3 class="text-sm font-medium text-slate-300">Top matches</h3>
                    <ul class="mt-3 space-y-3">
                        @foreach ($searchResults as $doc)
                            <li class="rounded-md border border-slate-800 bg-slate-950/60 px-3 py-3 text-sm">
                                <p class="font-medium text-slate-100">
                                    {{ $doc->candidate?->display_name ?: 'Candidate' }}
                                    <span class="text-slate-500">· {{ $doc->original_name }}</span>
                                </p>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @elseif ($searchResults !== null && $searchResults->isEmpty())
                <p class="mt-4 text-sm text-slate-500">No matches for that query.</p>
            @endif
        </section>

        <section>
            <h2 class="mb-4 text-lg font-medium text-white">Recent candidates</h2>
            @if ($candidates->isEmpty())
                <p class="text-sm text-slate-500">No CVs yet. Share your public portal link to collect uploads.</p>
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
@endsection
