@extends('layouts.app')

@section('title', 'Create agency — '.config('app.name'))

@section('content')
    <div class="mx-auto max-w-md">
        <x-page-header
            title="Create your agency"
            lead="You will receive a public link to share with candidates, for example /agency/your-agency."
            class="mb-8"
        />

        <div class="app-card">
            <form method="POST" action="{{ route('register.store') }}" class="space-y-5" novalidate>
                @csrf
                <div>
                    <label for="organization_name" class="form-label">Agency name</label>
                    <input
                        id="organization_name"
                        name="organization_name"
                        type="text"
                        value="{{ old('organization_name') }}"
                        required
                        class="form-input"
                        autocomplete="organization"
                    />
                </div>
                <div>
                    <label for="organization_slug" class="form-label">
                        URL name <span class="font-normal text-slate-500">(optional)</span>
                    </label>
                    <input
                        id="organization_slug"
                        name="organization_slug"
                        type="text"
                        value="{{ old('organization_slug') }}"
                        pattern="[a-z0-9]+(?:-[a-z0-9]+)*"
                        class="form-input"
                        placeholder="e.g. acme-recruiting"
                    />
                    <p class="mt-1.5 text-xs text-slate-500">Lowercase letters, numbers, and hyphens. Leave blank to auto-generate.</p>
                </div>
                <div>
                    <label for="admin_name" class="form-label">Your name</label>
                    <input
                        id="admin_name"
                        name="admin_name"
                        type="text"
                        value="{{ old('admin_name') }}"
                        required
                        class="form-input"
                        autocomplete="name"
                    />
                </div>
                <div>
                    <label for="email" class="form-label">Work email</label>
                    <input
                        id="email"
                        name="email"
                        type="email"
                        value="{{ old('email') }}"
                        required
                        class="form-input"
                        autocomplete="username"
                    />
                </div>
                <div>
                    <label for="password" class="form-label">Password</label>
                    <input
                        id="password"
                        name="password"
                        type="password"
                        required
                        class="form-input"
                        autocomplete="new-password"
                    />
                </div>
                <div>
                    <label for="password_confirmation" class="form-label">Confirm password</label>
                    <input
                        id="password_confirmation"
                        name="password_confirmation"
                        type="password"
                        required
                        class="form-input"
                        autocomplete="new-password"
                    />
                </div>
                <button type="submit" class="btn-primary w-full">Create agency &amp; log in</button>
            </form>
        </div>
    </div>
@endsection
