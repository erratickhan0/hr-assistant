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

        $candidates = Candidate::query()
            ->where('organization_id', $organization->id)
            ->with(['documents' => fn ($q) => $q->latest()])
            ->latest()
            ->paginate(15);

        $searchQuery = $request->session()->get('search_query');
        $searchResultIds = $request->session()->get('search_result_ids');

        $searchResults = null;
        if (is_array($searchResultIds) && $searchResultIds !== []) {
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
            'candidates' => $candidates,
            'searchQuery' => is_string($searchQuery) ? $searchQuery : null,
            'searchResults' => $searchResults,
        ]);
    }
}
