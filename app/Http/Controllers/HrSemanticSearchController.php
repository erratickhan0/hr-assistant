<?php

namespace App\Http\Controllers;

use App\Http\Requests\Hr\StoreSemanticSearchRequest;
use App\Models\Candidate;
use App\Services\HrCandidateSearchService;
use Illuminate\Http\RedirectResponse;

class HrSemanticSearchController extends Controller
{
    public function __invoke(StoreSemanticSearchRequest $request, HrCandidateSearchService $search): RedirectResponse
    {
        $this->authorize('viewAny', Candidate::class);

        $organization = $request->user()->organization;
        if ($organization === null) {
            abort(403);
        }

        $q = $request->validated('q');
        $results = $search->search($organization, $q);

        return redirect()
            ->route('dashboard')
            ->with('search_query', $q)
            ->with('search_result_ids', $results->pluck('id')->all());
    }
}
