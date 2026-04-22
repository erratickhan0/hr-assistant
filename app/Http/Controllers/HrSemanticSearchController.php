<?php

namespace App\Http\Controllers;

use App\Http\Requests\Hr\StoreSemanticSearchRequest;
use App\Models\Candidate;
use App\Services\HrCandidateSearchService;
use App\Services\OpenAICvAnswerService;
use Illuminate\Http\RedirectResponse;

class HrSemanticSearchController extends Controller
{
    public function __invoke(
        StoreSemanticSearchRequest $request,
        HrCandidateSearchService $search,
        OpenAICvAnswerService $cvAnswer,
    ): RedirectResponse {
        $this->authorize('viewAny', Candidate::class);

        $organization = $request->user()->organization;
        if ($organization === null) {
            abort(403);
        }

        $q = $request->validated('q');
        $rows = $search->searchWithScores($organization, $q);
        $documents = collect($rows)->pluck('document')->values();
        $evidence = $cvAnswer->evidenceForDocuments($q, $documents);

        $filteredDocuments = $documents->filter(function ($doc) use ($evidence): bool {
            return isset($evidence[$doc->id]);
        })->values();

        if ($filteredDocuments->isEmpty()) {
            $filteredDocuments = $documents;
        }

        $ids = $filteredDocuments->pluck('id')->all();

        $answer = null;
        if ($cvAnswer->isConfigured() && $filteredDocuments->isNotEmpty()) {
            $answer = $cvAnswer->answerForDocuments($q, $filteredDocuments);
        }

        return redirect()
            ->route('dashboard')
            ->with('search_query', $q)
            ->with('search_result_ids', $ids)
            ->with('search_mode', 'semantic_text')
            ->with('search_evidence', $evidence)
            ->with('search_answer', $answer);
    }
}
