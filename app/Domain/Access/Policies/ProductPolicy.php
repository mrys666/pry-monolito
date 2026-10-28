<?php

namespace App\Domain\Access\Policies;

use App\Domain\Access\Enums\UserRole;
use App\Domain\Catalog\Models\Product;
use App\Models\User;

class ProductPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->role === UserRole::Admin;
    }

    public function update(User $user, Product $product): bool
    {
        return $user->role === UserRole::Admin;
    }
}
