<?php

namespace App\Domain\Access\Policies;

use App\Domain\Access\Enums\UserRole;
use App\Models\User;

class InventoryMovementPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function registerFlow(User $user): bool
    {
        return in_array($user->role, [UserRole::Admin, UserRole::Operator], true);
    }

    public function adjustStock(User $user): bool
    {
        return $user->role === UserRole::Admin;
    }
}
