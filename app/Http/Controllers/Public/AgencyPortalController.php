<?php

namespace App\Http\Controllers\Public;

use App\Enums\CandidateDocumentProcessingStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Public\StoreCandidateCvRequest;
use App\Jobs\ProcessCvUploadJob;
use App\Models\Candidate;
use App\Models\CandidateDocument;
use App\Models\Organization;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class AgencyPortalController extends Controller
{
    public function show(Organization $organization): View
    {
        return view('public.agency-portal', [
            'organization' => $organization,
        ]);
    }

    public function store(StoreCandidateCvRequest $request, Organization $organization): RedirectResponse
    {
        $file = $request->file('cv');
        $disk = (string) config('filesystems.default', 'local');
        $path = $file->store("cvs/organization-{$organization->id}", $disk);

        $candidate = Candidate::query()->create([
            'organization_id' => $organization->id,
            'display_name' => $request->validated('display_name'),
            'email' => $request->validated('email'),
            'phone' => $request->validated('phone'),
            'source' => 'public_upload',
            'metadata' => null,
        ]);

        $document = CandidateDocument::query()->create([
            'candidate_id' => $candidate->id,
            'original_name' => $file->getClientOriginalName(),
            'disk' => $disk,
            'path' => $path,
            'mime' => (string) $file->getMimeType(),
            'size_bytes' => $file->getSize() ?: 0,
            'extracted_text_path' => null,
            'processing_status' => CandidateDocumentProcessingStatus::Pending,
            'pinecone_vector_id' => null,
            'embedding_model' => null,
            'embedding_dimensions' => null,
            'indexed_at' => null,
            'last_error' => null,
        ]);

        ProcessCvUploadJob::dispatch($document);

        return redirect()
            ->route('agency.portal', $organization)
            ->with('status', __('Thank you — your CV was received.'));
    }
}
