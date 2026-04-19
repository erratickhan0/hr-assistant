<?php

namespace App\Policies;

use App\Models\CandidateDocument;
use App\Models\User;

class CandidateDocumentPolicy
{
    public function view(User $user, CandidateDocument $document): bool
    {
        $candidate = $document->candidate;

        if ($candidate === null) {
            return false;
        }

        return (int) $user->organization_id === (int) $candidate->organization_id;
    }
}
