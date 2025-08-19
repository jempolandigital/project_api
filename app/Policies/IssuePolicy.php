<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Issue;

class IssuePolicy
{
    public function view(User $user, Issue $issue)
    {
        // SPV hanya bisa lihat issue tenant dia
        return $user->tenantMappings()->pluck('tenant_id')->contains($issue->tenant_id);
    }

    public function update(User $user, Issue $issue)
    {
        // Hanya SPV di tenant yang sama
        return $user->hasRole('SPV') &&
               $user->tenantMappings()->pluck('tenant_id')->contains($issue->tenant_id);
    }

    public function create(User $user)
    {
        // Semua user bisa buat issue
        return true;
    }
}
