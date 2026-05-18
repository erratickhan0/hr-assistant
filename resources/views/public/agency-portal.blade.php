@extends('layouts.app')

@section('title', $organization->name.' — Submit CV')
@section('hide_guest_auth_links', '1')
@section('hide_logout_button', '1')

@section('content')
    <div class="mx-auto max-w-lg">
        <x-page-header
            eyebrow="Submit your CV"
            :title="$organization->name"
            lead="Upload your CV as a PDF or Word document (max 10 MB). Contact fields are optional."
            class="mb-8"
        />

        <div class="app-card">
            <form method="POST" action="{{ route('agency.cv.store', $organization) }}" enctype="multipart/form-data" class="space-y-5">
                @csrf
                <div>
                    <label for="display_name" class="form-label">
                        Your name <span class="font-normal text-slate-500">(optional)</span>
                    </label>
                    <input
                        id="display_name"
                        name="display_name"
                        type="text"
                        value="{{ old('display_name') }}"
                        class="form-input"
                    />
                </div>
                <div>
                    <label for="email" class="form-label">
                        Email <span class="font-normal text-slate-500">(optional)</span>
                    </label>
                    <input
                        id="email"
                        name="email"
                        type="email"
                        value="{{ old('email') }}"
                        class="form-input"
                    />
                </div>
                <div>
                    <label for="phone" class="form-label">
                        Phone <span class="font-normal text-slate-500">(optional)</span>
                    </label>
                    <input
                        id="phone"
                        name="phone"
                        type="text"
                        value="{{ old('phone') }}"
                        class="form-input"
                    />
                </div>
                <div>
                    <label for="cv" class="form-label">CV file</label>
                    <input
                        id="cv"
                        name="cv"
                        type="file"
                        required
                        accept=".pdf,.doc,.docx,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document"
                        class="block w-full text-sm text-slate-300 file:mr-4 file:cursor-pointer file:rounded-lg file:border-0 file:bg-slate-800 file:px-4 file:py-2.5 file:text-sm file:font-medium file:text-slate-100 hover:file:bg-slate-700"
                    />
                </div>
                <button type="submit" class="btn-primary w-full">Submit CV</button>
            </form>
        </div>
    </div>
@endsection
