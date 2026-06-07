<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', config('app.name'))</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
</head>
<body class="app-bg flex min-h-screen flex-col">
    <header class="app-header">
        <div class="mx-auto flex max-w-6xl flex-wrap items-center justify-between gap-4 px-4 py-4 sm:px-6 lg:px-8">
            <a href="{{ route('home') }}" class="group flex items-center gap-2.5">
                <span class="flex size-9 items-center justify-center rounded-lg bg-emerald-500/15 ring-1 ring-emerald-500/30">
                    <svg class="size-5 text-emerald-400" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <path d="M9 12h6m-6 4h6m2 5H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5.586a1 1 0 0 1 .707.293l5.414 5.414a1 1 0 0 1 .293.707V19a2 2 0 0 1-2 2z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </span>
                <span class="text-lg font-semibold tracking-tight text-white group-hover:text-emerald-50">
                    {{ config('app.name') }}
                </span>
            </a>
            <nav class="flex flex-wrap items-center gap-1">
                @auth
                    <x-nav-link :href="route('pages.how-it-works')" :active="request()->routeIs('pages.how-it-works')">How it works</x-nav-link>
                    <x-nav-link :href="route('pages.faq')" :active="request()->routeIs('pages.faq')">FAQ</x-nav-link>
                    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">Dashboard</x-nav-link>
                    <x-nav-link :href="route('candidates.index')" :active="request()->routeIs('candidates.*')">Candidates</x-nav-link>
                    @if (auth()->user()->organization)
                        <x-nav-link
                            :href="route('agency.portal', auth()->user()->organization)"
                            :active="request()->routeIs('agency.*')"
                            target="_blank"
                            rel="noopener"
                        >
                            Portal
                        </x-nav-link>
                    @endif
                    @unless (View::hasSection('hide_logout_button'))
                        <form method="POST" action="{{ route('logout') }}" class="ml-1 inline">
                            @csrf
                            <button type="submit" class="nav-link">Log out</button>
                        </form>
                    @endunless
                @else
                    <x-nav-link :href="route('pages.how-it-works')" :active="request()->routeIs('pages.how-it-works')">How it works</x-nav-link>
                    <x-nav-link :href="route('pages.faq')" :active="request()->routeIs('pages.faq')">FAQ</x-nav-link>
                    @unless (View::hasSection('hide_guest_auth_links'))
                        <x-nav-link :href="route('login')" :active="request()->routeIs('login')">Log in</x-nav-link>
                        <a href="{{ route('register') }}" class="btn-primary ml-1">Create agency</a>
                    @endunless
                @endauth
            </nav>
        </div>
    </header>

    <main class="mx-auto w-full max-w-6xl flex-1 px-4 py-10 sm:px-6 sm:py-12 lg:px-8">
        @if (session('status'))
            <div class="alert-success mb-8" role="status">
                {{ session('status') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="alert-error mb-8" role="alert">
                <p class="mb-2 font-medium">Please fix the following:</p>
                <ul class="list-inside list-disc space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @yield('content')
    </main>

    @stack('scripts')

    <footer class="mt-auto border-t border-slate-800/60 bg-slate-950/50">
        <div class="mx-auto flex max-w-6xl flex-col gap-4 px-4 py-8 text-sm text-slate-500 sm:flex-row sm:items-center sm:justify-between sm:px-6 lg:px-8">
            <p>&copy; {{ date('Y') }} {{ config('app.name') }}. Built for recruitment teams.</p>
            <div class="flex flex-wrap gap-4">
                <a href="{{ route('pages.how-it-works') }}" class="transition hover:text-slate-300">How it works</a>
                <a href="{{ route('pages.faq') }}" class="transition hover:text-slate-300">FAQ</a>
                @guest
                    <a href="{{ route('login') }}" class="transition hover:text-slate-300">Log in</a>
                @endguest
            </div>
        </div>
    </footer>
    @if (request()->routeIs('home', 'pages.how-it-works', 'pages.faq', 'agency.portal'))
        <script src="https://customer-support.tech-gap.com/widget.js" data-api-key="wk_live_T47unUeexxL2E4vkP7gifXj9rGJZuf7AmFyZSTzr" async></script>
    @endif
</body>
</html>
