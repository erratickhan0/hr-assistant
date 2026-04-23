@extends('layouts.app')

@section('title', config('app.name').' — HR assistant MVP')

@section('content')
    <div class="max-w-2xl space-y-6">
        <h1 class="text-3xl font-semibold tracking-tight text-white sm:text-4xl">
            Multi-tenant HR assistant (MVP)
        </h1>
        <p class="text-slate-400">
            Register an agency, share your public portal link, collect CVs, then review uploads from the dashboard.
            Semantic search &amp; Pinecone wiring come next.
        </p>
        <div class="flex flex-wrap gap-2 text-sm">
            <a
                href="{{ route('pages.how-it-works') }}"
                class="rounded-md border border-slate-700 px-3 py-1.5 text-slate-300 hover:bg-slate-900 hover:text-white"
            >
                How it works
            </a>
        </div>
        <div class="flex flex-wrap gap-3">
            @auth
                <a
                    href="{{ route('dashboard') }}"
                    class="inline-flex rounded-md bg-emerald-500 px-4 py-2 text-sm font-medium text-slate-950 hover:bg-emerald-400"
                >
                    Go to dashboard
                </a>
            @else
                <a
                    href="{{ route('register') }}"
                    class="inline-flex rounded-md bg-emerald-500 px-4 py-2 text-sm font-medium text-slate-950 hover:bg-emerald-400"
                >
                    Create agency
                </a>
                <a
                    href="{{ route('login') }}"
                    class="inline-flex rounded-md border border-slate-600 px-4 py-2 text-sm font-medium text-slate-200 hover:border-slate-500 hover:bg-slate-900"
                >
                    Log in
                </a>
            @endauth
        </div>
    </div>
@endsection
