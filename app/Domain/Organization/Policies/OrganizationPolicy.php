<?php

namespace App\Domain\Organization\Policies;

use App\Domain\Access\Enums\UserRole;
use App\Domain\Organization\Models\Organization;
use App\Models\User;

class OrganizationPolicy
{
    /**
     * Determina si el usuario puede gestionar la organización (ajustes y usuarios).
     */
    public function manage(User $user, Organization $organization): bool
    {
        return $user->role === UserRole::Admin
            && (int) $user->organization_id === (int) $organization->id;
    }
}
