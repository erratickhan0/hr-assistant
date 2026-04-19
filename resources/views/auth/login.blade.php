@extends('layouts.app')

@section('title', 'Log in — '.config('app.name'))

@section('content')
    <div class="mx-auto max-w-md space-y-8">
        <div>
            <h1 class="text-2xl font-semibold text-white">Log in</h1>
            <p class="mt-2 text-sm text-slate-400">Use your agency slug plus the email you registered with.</p>
        </div>

        <form method="POST" action="{{ route('login.store') }}" class="space-y-5">
            @csrf
            <div>
                <label for="organization_slug" class="mb-1 block text-sm font-medium text-slate-300">Agency slug</label>
                <input
                    id="organization_slug"
                    name="organization_slug"
                    type="text"
                    value="{{ old('organization_slug', $organization_slug) }}"
                    required
                    class="w-full rounded-md border border-slate-700 bg-slate-900 px-3 py-2 text-white placeholder:text-slate-500 focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500"
                    placeholder="e.g. demo-agency"
                    autocomplete="off"
                />
            </div>
            <div>
                <label for="email" class="mb-1 block text-sm font-medium text-slate-300">Email</label>
                <input
                    id="email"
                    name="email"
                    type="email"
                    value="{{ old('email') }}"
                    required
                    class="w-full rounded-md border border-slate-700 bg-slate-900 px-3 py-2 text-white placeholder:text-slate-500 focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500"
                    autocomplete="username"
                />
            </div>
            <div>
                <label for="password" class="mb-1 block text-sm font-medium text-slate-300">Password</label>
                <input
                    id="password"
                    name="password"
                    type="password"
                    required
                    class="w-full rounded-md border border-slate-700 bg-slate-900 px-3 py-2 text-white placeholder:text-slate-500 focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500"
                    autocomplete="current-password"
                />
            </div>
            <button
                type="submit"
                class="w-full rounded-md bg-emerald-500 py-2.5 text-sm font-semibold text-slate-950 hover:bg-emerald-400"
            >
                Log in
            </button>
        </form>
    </div>
@endsection
