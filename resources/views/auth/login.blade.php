@extends('layouts.app')

@section('title', 'Log in — '.config('app.name'))

@section('content')
    <div class="mx-auto max-w-md">
        <x-page-header
            title="Log in"
            lead="Enter your agency URL name and the email you used when you signed up."
            class="mb-8"
        />

        <div class="app-card">
            <form method="POST" action="{{ route('login.store') }}" class="space-y-5">
                @csrf
                <div>
                    <label for="organization_slug" class="form-label">Agency URL name</label>
                    <input
                        id="organization_slug"
                        name="organization_slug"
                        type="text"
                        value="{{ old('organization_slug', $organization_slug) }}"
                        required
                        class="form-input"
                        placeholder="e.g. demo-agency"
                        autocomplete="off"
                    />
                </div>
                <div>
                    <label for="email" class="form-label">Email</label>
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
                        autocomplete="current-password"
                    />
                </div>
                <button type="submit" class="btn-primary w-full">Log in</button>
            </form>
        </div>
    </div>
@endsection
