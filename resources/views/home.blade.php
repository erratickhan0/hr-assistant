@extends('layouts.app')

@section('title', config('app.name').' — Recruitment CV assistant')

@section('content')
    <div class="space-y-16">
        <section class="grid gap-10 lg:grid-cols-2 lg:items-center lg:gap-12">
            <div class="space-y-6">
                <div class="space-y-4">
                    <p class="page-eyebrow">Recruitment made simpler</p>
                    <h1 class="page-title text-balance">
                        Collect, organise, and search candidate CVs in one place
                    </h1>
                    <p class="page-lead">
                        Give your agency a branded portal link. Candidates upload their CV; you review and shortlist from a clean dashboard when roles open up.
                    </p>
                </div>
                <div class="flex flex-wrap gap-3">
                    @auth
                        <a href="{{ route('dashboard') }}" class="btn-primary">Go to dashboard</a>
                        <a href="{{ route('candidates.index') }}" class="btn-secondary">View candidates</a>
                    @else
                        <a href="{{ route('register') }}" class="btn-primary">Create agency account</a>
                        <a href="{{ route('login') }}" class="btn-secondary">Log in</a>
                    @endauth
                </div>
            </div>

            <div class="space-y-3">
                <p class="text-sm font-medium text-slate-300">Watch the demo</p>
                <x-youtube-embed
                    video-id="n5-86E0vYv0"
                    title="HR Assistant product demo"
                />
                <p class="text-xs leading-relaxed text-slate-500">
                    See how to share your portal, receive CVs, and search candidates in under two minutes.
                </p>
            </div>
        </section>

        <section class="grid gap-5 sm:grid-cols-3">
            <article class="app-card">
                <div class="mb-4 flex size-10 items-center justify-center rounded-lg bg-emerald-500/10 ring-1 ring-emerald-500/20">
                    <svg class="size-5 text-emerald-400" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <path d="M13.828 10.172a4 4 0 0 0-5.656 0l-4 4a4 4 0 1 0 5.656 5.656l1.102-1.101" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                        <path d="M10.172 13.828a4 4 0 0 0 5.656 0l4-4a4 4 0 0 0-5.656-5.656l-1.1 1.1" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                    </svg>
                </div>
                <h2 class="section-title">Share one link</h2>
                <p class="mt-2 text-sm leading-relaxed text-slate-400">
                    Send your portal URL to candidates by email or WhatsApp. No accounts required on their side.
                </p>
            </article>
            <article class="app-card">
                <div class="mb-4 flex size-10 items-center justify-center rounded-lg bg-emerald-500/10 ring-1 ring-emerald-500/20">
                    <svg class="size-5 text-emerald-400" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <path d="M9 12h6m-6 4h6m2 5H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5.586a1 1 0 0 1 .707.293l5.414 5.414a1 1 0 0 1 .293.707V19a2 2 0 0 1-2 2z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </div>
                <h2 class="section-title">Every CV in one inbox</h2>
                <p class="mt-2 text-sm leading-relaxed text-slate-400">
                    Submissions land in your candidate list with contact details and files attached.
                </p>
            </article>
            <article class="app-card">
                <div class="mb-4 flex size-10 items-center justify-center rounded-lg bg-emerald-500/10 ring-1 ring-emerald-500/20">
                    <svg class="size-5 text-emerald-400" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <path d="m21 21-4.3-4.3M11 18a7 7 0 1 0 0-14 7 7 0 0 0 0 14z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                    </svg>
                </div>
                <h2 class="section-title">Search when you hire</h2>
                <p class="mt-2 text-sm leading-relaxed text-slate-400">
                    Find people by skills, roles, or experience—without opening every file manually.
                </p>
            </article>
        </section>

        <section class="app-card flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="section-title">New here?</h2>
                <p class="mt-1 text-sm text-slate-400">Read the step-by-step guide or browse common questions.</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('pages.how-it-works') }}" class="btn-secondary">How it works</a>
                <a href="{{ route('pages.faq') }}" class="btn-ghost">FAQ</a>
            </div>
        </section>
    </div>
@endsection
