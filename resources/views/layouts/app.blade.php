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
<body class="min-h-screen bg-slate-950 font-sans text-slate-100 antialiased">
    <header class="border-b border-slate-800/80">
        <div class="mx-auto flex max-w-5xl flex-wrap items-center justify-between gap-3 px-4 py-4">
            <a href="{{ route('home') }}" class="text-lg font-semibold tracking-tight text-emerald-400 hover:text-emerald-300">
                {{ config('app.name') }}
            </a>
            <nav class="flex flex-wrap items-center gap-2 text-sm">
                @auth
                    <a
                        href="{{ route('dashboard') }}"
                        class="rounded-md px-3 py-1.5 text-slate-300 hover:bg-slate-800 hover:text-white"
                    >
                        Dashboard
                    </a>
                    @if (auth()->user()->organization)
                        <a
                            href="{{ route('agency.portal', auth()->user()->organization) }}"
                            class="rounded-md px-3 py-1.5 text-slate-300 hover:bg-slate-800 hover:text-white"
                            target="_blank"
                            rel="noopener"
                        >
                            Public portal
                        </a>
                    @endif
                    <form method="POST" action="{{ route('logout') }}" class="inline">
                        @csrf
                        <button
                            type="submit"
                            class="rounded-md px-3 py-1.5 text-slate-300 hover:bg-slate-800 hover:text-white"
                        >
                            Log out
                        </button>
                    </form>
                @else
                    @unless (View::hasSection('hide_guest_auth_links'))
                        <a
                            href="{{ route('login') }}"
                            class="rounded-md px-3 py-1.5 text-slate-300 hover:bg-slate-800 hover:text-white"
                        >
                            Log in
                        </a>
                        <a
                            href="{{ route('register') }}"
                            class="rounded-md bg-emerald-500 px-3 py-1.5 font-medium text-slate-950 hover:bg-emerald-400"
                        >
                            Create agency
                        </a>
                    @endunless
                @endauth
            </nav>
        </div>
    </header>

    <main class="mx-auto max-w-5xl px-4 py-10">
        @if (session('status'))
            <div
                class="mb-6 rounded-lg border border-emerald-700/40 bg-emerald-950/50 px-4 py-3 text-sm text-emerald-100"
                role="status"
            >
                {{ session('status') }}
            </div>
        @endif

        @if ($errors->any())
            <div
                class="mb-6 rounded-lg border border-red-800/60 bg-red-950/40 px-4 py-3 text-sm text-red-100"
                role="alert"
            >
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
</body>
</html>
