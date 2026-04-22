<?php

namespace App\Http\Controllers;

use App\Models\Candidate;
use App\Models\CandidateDocument;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $this->authorize('viewAny', Candidate::class);

        $organization = $request->user()->organization;
        if ($organization === null) {
            abort(403);
        }

        $searchQuery = $request->session()->get('search_query');
        $searchResultIds = $request->session()->get('search_result_ids');
        $searchMode = $request->session()->get('search_mode');
        $searchEvidence = $request->session()->get('search_evidence');
        $searchAnswer = $request->session()->get('search_answer');

        $searchResults = null;
        if ($searchMode === 'semantic_text' && is_array($searchResultIds) && $searchResultIds !== []) {
            $searchResults = CandidateDocument::query()
                ->whereIn('id', $searchResultIds)
                ->whereHas('candidate', fn ($q) => $q->where('organization_id', $organization->id))
                ->with('candidate')
                ->get()
                ->sortBy(fn (CandidateDocument $doc) => array_search($doc->id, $searchResultIds, true))
                ->values();
        }

        return view('dashboard', [
            'organization' => $organization,
            'searchQuery' => is_string($searchQuery) ? $searchQuery : null,
            'searchResults' => $searchResults,
            'searchEvidence' => is_array($searchEvidence) ? $searchEvidence : [],
            'searchAnswer' => is_string($searchAnswer) ? $searchAnswer : null,
        ]);
    }
}
