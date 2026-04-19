<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreSemanticSearchApiRequest;
use App\Http\Resources\Api\CandidateDocumentResource;
use App\Models\Candidate;
use App\Services\HrCandidateSearchService;
use Illuminate\Http\JsonResponse;

class SemanticSearchController extends Controller
{
    public function store(StoreSemanticSearchApiRequest $request, HrCandidateSearchService $search): JsonResponse
    {
        $this->authorize('viewAny', Candidate::class);

        $organization = $request->user()->organization;
        if ($organization === null) {
            abort(403);
        }

        $q = $request->validated('q');
        $rows = $search->searchWithScores($organization, $q);

        $matches = [];
        foreach ($rows as $row) {
            $document = $row['document'];
            $document->loadMissing('candidate');
            $matches[] = [
                'similarity' => $row['score'],
                'document' => (new CandidateDocumentResource($document))->resolve($request),
            ];
        }

        return response()->json([
            'data' => [
                'query' => $q,
                'matches' => $matches,
            ],
        ]);
    }
}
