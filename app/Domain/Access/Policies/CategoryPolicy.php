<?php

namespace App\Domain\Access\Policies;

use App\Domain\Access\Enums\UserRole;
use App\Domain\Catalog\Models\Category;
use App\Models\User;

class CategoryPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->role === UserRole::Admin;
    }

    public function update(User $user, Category $category): bool
    {
        return $user->role === UserRole::Admin;
    }

    public function delete(User $user, Category $category): bool
    {
        return $user->role === UserRole::Admin;
    }
}
