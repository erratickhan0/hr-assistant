@extends('layouts.app')

@section('title', 'FAQ — '.config('app.name'))

@section('content')
    <div class="mx-auto max-w-3xl space-y-10">
        <x-page-header
            eyebrow="Help"
            title="Frequently asked questions"
            lead="Quick answers about collecting CVs, searching your talent pool, and managing submissions."
        />

        <div class="space-y-4">
            <section class="app-card">
                <h2 class="section-title">How do I share my portal with candidates?</h2>
                <p class="mt-2 text-sm leading-relaxed text-slate-300">
                    After you sign in, open your dashboard and copy the public portal link. Send that link by email, WhatsApp, or your careers page. Candidates use it to upload their CV without needing an account.
                </p>
            </section>

            <section class="app-card">
                <h2 class="section-title">Why is a CV still showing as pending?</h2>
                <p class="mt-2 text-sm leading-relaxed text-slate-300">
                    Pending means the CV was received but is still being prepared for search. This usually finishes within a minute. If it stays pending for longer, check back shortly or contact your administrator.
                </p>
            </section>

            <section class="app-card">
                <h2 class="section-title">What file types can candidates upload?</h2>
                <p class="mt-2 text-sm leading-relaxed text-slate-300">
                    PDF and Word documents are supported, up to 10&nbsp;MB per file. PDFs can be opened in your browser using View; Word files can be downloaded.
                </p>
            </section>

            <section class="app-card">
                <h2 class="section-title">How does search work?</h2>
                <p class="mt-2 text-sm leading-relaxed text-slate-300">
                    Type what you are looking for in plain language—for example, a job title, skill, or industry. The system finds relevant CVs and highlights matching phrases where possible.
                </p>
            </section>

            <section class="app-card">
                <h2 class="section-title">What is the difference between View and Download?</h2>
                <p class="mt-2 text-sm leading-relaxed text-slate-300">
                    <strong class="font-medium text-slate-200">View</strong> opens a PDF in a new browser tab.
                    <strong class="font-medium text-slate-200">Download</strong> saves a copy to your device. View is available for PDFs only.
                </p>
            </section>

            <section class="app-card">
                <h2 class="section-title">Can I find candidates by name or email?</h2>
                <p class="mt-2 text-sm leading-relaxed text-slate-300">
                    Yes. Use the Candidates page to search by name, email, or file name. The dashboard search is best for skills and experience across your whole pool.
                </p>
            </section>

            <section class="app-card">
                <h2 class="section-title">I forgot my agency URL name. What should I do?</h2>
                <p class="mt-2 text-sm leading-relaxed text-slate-300">
                    It is the short name in your portal URL (for example, <span class="text-slate-400">/agency/your-agency</span>). Check any link you previously shared with candidates, or ask a colleague who has access.
                </p>
            </section>
        </div>

        <p class="text-center text-sm text-slate-500">
            New to the platform?
            <a href="{{ route('pages.how-it-works') }}" class="link-accent">Read how it works</a>
        </p>
    </div>
@endsection
