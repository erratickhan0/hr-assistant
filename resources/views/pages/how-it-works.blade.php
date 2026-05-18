@extends('layouts.app')

@section('title', 'How it works — '.config('app.name'))

@section('content')
    <div class="mx-auto max-w-3xl space-y-10">
        <x-page-header
            eyebrow="Overview"
            title="How it works"
            lead="A simple workflow for recruitment agencies: share a link, collect CVs, and find the right candidates when you need them."
        />

        <div class="space-y-4">
            <section class="app-card">
                <p class="text-xs font-semibold text-emerald-400/90">Step 1</p>
                <h2 class="section-title mt-2">Set up your agency</h2>
                <p class="mt-2 text-sm leading-relaxed text-slate-300">
                    Create your account and receive a dedicated portal link. Share that link with candidates so they can submit their CV directly to you.
                </p>
            </section>

            <section class="app-card">
                <p class="text-xs font-semibold text-emerald-400/90">Step 2</p>
                <h2 class="section-title mt-2">Candidates submit their CV</h2>
                <p class="mt-2 text-sm leading-relaxed text-slate-300">
                    Candidates upload a PDF or Word file through your public portal, along with optional contact details. Each submission is saved securely to your account.
                </p>
            </section>

            <section class="app-card">
                <p class="text-xs font-semibold text-emerald-400/90">Step 3</p>
                <h2 class="section-title mt-2">CVs are prepared for search</h2>
                <p class="mt-2 text-sm leading-relaxed text-slate-300">
                    After upload, each CV is processed automatically. The system reads the content and makes it searchable, so you do not need to open files one by one.
                </p>
                <p class="mt-2 text-sm text-slate-400">
                    Processing usually completes within a minute. Check status on the Candidates page.
                </p>
            </section>

            <section class="app-card">
                <p class="text-xs font-semibold text-emerald-400/90">Step 4</p>
                <h2 class="section-title mt-2">Search, review, and shortlist</h2>
                <p class="mt-2 text-sm leading-relaxed text-slate-300">
                    From your dashboard, search using everyday language—skills, roles, or experience. Open PDFs in a new browser tab or download files when you need a copy.
                </p>
            </section>
        </div>

        <p class="text-center text-sm text-slate-500">
            Questions?
            <a href="{{ route('pages.faq') }}" class="link-accent">See the FAQ</a>
        </p>
    </div>
@endsection
