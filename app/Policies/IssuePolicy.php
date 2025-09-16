<?php

namespace App\Policies;

use App\Models\User;
use App\Models\IssueTracker;

class IssuePolicy
{
    public function view(User $user, IssueTracker $issue)
    {
        // SPV hanya bisa lihat issue tenant dia
        return $user->tenantMappings()->pluck('tenant_id')->contains($issue->tenant_id);
    }

    public function update(User $user, IssueTracker $issue)
    {
        // Hanya SPV di tenant yang sama
        return $user->hasRole('spv') &&
               $user->tenantMappings()->pluck('tenant_id')->contains($issue->tenant_id);
    }

    public function create(User $user)
    {
        // Semua user bisa buat issue
        return true;
    }
}
