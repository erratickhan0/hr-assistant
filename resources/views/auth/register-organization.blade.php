@extends('layouts.app')

@section('title', 'Create agency — '.config('app.name'))

@section('content')
    <div class="mx-auto max-w-md space-y-8">
        <div>
            <h1 class="text-2xl font-semibold text-white">Create your agency</h1>
            <p class="mt-2 text-sm text-slate-400">You’ll get a public link like <code class="text-emerald-300/90">/agency/your-slug</code> for candidates.</p>
        </div>

        <form method="POST" action="{{ route('register.store') }}" class="space-y-5" novalidate>
            @csrf
            <div>
                <label for="organization_name" class="mb-1 block text-sm font-medium text-slate-300">Agency name</label>
                <input
                    id="organization_name"
                    name="organization_name"
                    type="text"
                    value="{{ old('organization_name') }}"
                    required
                    class="w-full rounded-md border border-slate-700 bg-slate-900 px-3 py-2 text-white placeholder:text-slate-500 focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500"
                    autocomplete="organization"
                />
            </div>
            <div>
                <label for="organization_slug" class="mb-1 block text-sm font-medium text-slate-300">
                    URL slug <span class="font-normal text-slate-500">(optional)</span>
                </label>
                <input
                    id="organization_slug"
                    name="organization_slug"
                    type="text"
                    value="{{ old('organization_slug') }}"
                    pattern="[a-z0-9]+(?:-[a-z0-9]+)*"
                    class="w-full rounded-md border border-slate-700 bg-slate-900 px-3 py-2 text-white placeholder:text-slate-500 focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500"
                    placeholder="e.g. acme-recruiting"
                />
                <p class="mt-1 text-xs text-slate-500">Lowercase letters, numbers, and hyphens only. Leave blank to auto-generate.</p>
            </div>
            <div>
                <label for="admin_name" class="mb-1 block text-sm font-medium text-slate-300">Your name</label>
                <input
                    id="admin_name"
                    name="admin_name"
                    type="text"
                    value="{{ old('admin_name') }}"
                    required
                    class="w-full rounded-md border border-slate-700 bg-slate-900 px-3 py-2 text-white placeholder:text-slate-500 focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500"
                    autocomplete="name"
                />
            </div>
            <div>
                <label for="email" class="mb-1 block text-sm font-medium text-slate-300">Work email</label>
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
                    autocomplete="new-password"
                />
            </div>
            <div>
                <label for="password_confirmation" class="mb-1 block text-sm font-medium text-slate-300">Confirm password</label>
                <input
                    id="password_confirmation"
                    name="password_confirmation"
                    type="password"
                    required
                    class="w-full rounded-md border border-slate-700 bg-slate-900 px-3 py-2 text-white placeholder:text-slate-500 focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500"
                    autocomplete="new-password"
                />
            </div>
            <button
                type="submit"
                class="w-full rounded-md bg-emerald-500 py-2.5 text-sm font-semibold text-slate-950 hover:bg-emerald-400"
            >
                Create agency &amp; log in
            </button>
        </form>
    </div>
@endsection
