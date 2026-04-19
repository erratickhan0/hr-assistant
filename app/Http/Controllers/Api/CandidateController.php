<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\CandidateResource;
use App\Models\Candidate;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CandidateController extends Controller
{
    public function index(Request $request): JsonResponse
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

        return CandidateResource::collection($candidates)->response();
    }
}
