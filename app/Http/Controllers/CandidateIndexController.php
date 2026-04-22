<?php

namespace App\Http\Controllers;

use App\Models\Candidate;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CandidateIndexController extends Controller
{
    public function __invoke(Request $request): View
    {
        $this->authorize('viewAny', Candidate::class);

        $organization = $request->user()->organization;
        if ($organization === null) {
            abort(403);
        }

        $search = trim((string) $request->query('q', ''));

        $candidates = Candidate::query()
            ->where('organization_id', $organization->id)
            ->when($search !== '', function ($query) use ($search): void {
                $escaped = str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], mb_strtolower($search));
                $query->where(function ($inner) use ($escaped): void {
                    $inner->whereRaw('LOWER(display_name) like ?', ['%'.$escaped.'%'])
                        ->orWhereRaw('LOWER(email) like ?', ['%'.$escaped.'%'])
                        ->orWhereHas('documents', fn ($docQuery) => $docQuery->whereRaw('LOWER(original_name) like ?', ['%'.$escaped.'%']));
                });
            })
            ->with(['documents' => fn ($q) => $q->latest()])
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('candidates.index', [
            'organization' => $organization,
            'candidates' => $candidates,
            'search' => $search !== '' ? $search : null,
        ]);
    }
}
