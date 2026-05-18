@props(['status'])

@php
    $class = match ($status->value ?? $status) {
        'pending' => 'status-badge-pending',
        'processing' => 'status-badge-processing',
        'ready' => 'status-badge-ready',
        'failed' => 'status-badge-failed',
        default => 'status-badge bg-slate-800 text-slate-300',
    };

    $label = $status instanceof \App\Enums\CandidateDocumentProcessingStatus
        ? $status->label()
        : ucfirst((string) $status);
@endphp

<span {{ $attributes->merge(['class' => $class]) }}>{{ $label }}</span>
