@extends('layouts.app')

@section('title', $organization->name.' — Submit CV')

@section('content')
    <div class="mx-auto max-w-lg space-y-8">
        <div>
            <p class="text-xs font-medium uppercase tracking-wider text-emerald-400/90">Public portal</p>
            <h1 class="mt-2 text-2xl font-semibold text-white">{{ $organization->name }}</h1>
            <p class="mt-2 text-sm text-slate-400">Upload your CV (PDF or Word). Max 10&nbsp;MB.</p>
        </div>

        <form method="POST" action="{{ route('agency.cv.store', $organization) }}" enctype="multipart/form-data" class="space-y-5">
            @csrf
            <div>
                <label for="display_name" class="mb-1 block text-sm font-medium text-slate-300">Your name <span class="font-normal text-slate-500">(optional)</span></label>
                <input
                    id="display_name"
                    name="display_name"
                    type="text"
                    value="{{ old('display_name') }}"
                    class="w-full rounded-md border border-slate-700 bg-slate-900 px-3 py-2 text-white placeholder:text-slate-500 focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500"
                />
            </div>
            <div>
                <label for="email" class="mb-1 block text-sm font-medium text-slate-300">Email <span class="font-normal text-slate-500">(optional)</span></label>
                <input
                    id="email"
                    name="email"
                    type="email"
                    value="{{ old('email') }}"
                    class="w-full rounded-md border border-slate-700 bg-slate-900 px-3 py-2 text-white placeholder:text-slate-500 focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500"
                />
            </div>
            <div>
                <label for="phone" class="mb-1 block text-sm font-medium text-slate-300">Phone <span class="font-normal text-slate-500">(optional)</span></label>
                <input
                    id="phone"
                    name="phone"
                    type="text"
                    value="{{ old('phone') }}"
                    class="w-full rounded-md border border-slate-700 bg-slate-900 px-3 py-2 text-white placeholder:text-slate-500 focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500"
                />
            </div>
            <div>
                <label for="cv" class="mb-1 block text-sm font-medium text-slate-300">CV file</label>
                <input
                    id="cv"
                    name="cv"
                    type="file"
                    required
                    accept=".pdf,.doc,.docx,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document"
                    class="block w-full text-sm text-slate-300 file:mr-4 file:rounded-md file:border-0 file:bg-slate-800 file:px-4 file:py-2 file:text-sm file:font-medium file:text-slate-100 hover:file:bg-slate-700"
                />
            </div>
            <button
                type="submit"
                class="w-full rounded-md bg-emerald-500 py-2.5 text-sm font-semibold text-slate-950 hover:bg-emerald-400"
            >
                Submit CV
            </button>
        </form>
    </div>
@endsection
