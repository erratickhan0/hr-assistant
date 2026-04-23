@extends('layouts.app')

@section('title', 'How it works — '.config('app.name'))

@section('content')
    <div class="mx-auto max-w-3xl space-y-8">
        <div class="space-y-3">
            <p class="text-xs font-medium uppercase tracking-wider text-emerald-400/90">Explainer</p>
            <h1 class="text-3xl font-semibold tracking-tight text-white sm:text-4xl">How the system works</h1>
            <p class="text-sm text-slate-400">
                This product helps agencies collect candidate CVs, process them in the background, and run semantic search on top of indexed resume data.
            </p>
        </div>

        <section class="space-y-4 rounded-lg border border-slate-800 bg-slate-900/40 p-5">
            <h2 class="text-lg font-semibold text-white">1) Agency setup</h2>
            <p class="text-sm text-slate-300">
                Agencies create an account and get access to a dashboard plus a unique public portal URL.
            </p>
        </section>

        <section class="space-y-4 rounded-lg border border-slate-800 bg-slate-900/40 p-5">
            <h2 class="text-lg font-semibold text-white">2) Candidate CV upload</h2>
            <p class="text-sm text-slate-300">
                Candidates open the public portal and submit their CV. The file is saved, and a processing job is queued for background execution.
            </p>
        </section>

        <section class="space-y-4 rounded-lg border border-slate-800 bg-slate-900/40 p-5">
            <h2 class="text-lg font-semibold text-white">3) Background processing and embeddings</h2>
            <p class="text-sm text-slate-300">
                The queued job extracts text from the CV, creates vector embeddings, and upserts vectors to Pinecone. Processing metadata is saved in the app database.
            </p>
            <p class="text-sm text-slate-400">
                Note: this requires queue workers plus valid OpenAI and Pinecone configuration.
            </p>
        </section>

        <section class="space-y-4 rounded-lg border border-slate-800 bg-slate-900/40 p-5">
            <h2 class="text-lg font-semibold text-white">4) Search and review</h2>
            <p class="text-sm text-slate-300">
                Recruiters can search candidates in the dashboard, open CVs directly in a new tab, and download files when needed.
            </p>
        </section>
    </div>
@endsection
