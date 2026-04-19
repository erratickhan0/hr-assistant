<?php

namespace App\Policies;

use App\Models\Candidate;
use App\Models\User;

class CandidatePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->organization_id !== null;
    }

    public function view(User $user, Candidate $candidate): bool
    {
        return (int) $user->organization_id === (int) $candidate->organization_id;
    }
}
