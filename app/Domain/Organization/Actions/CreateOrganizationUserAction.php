<?php

namespace App\Domain\Organization\Actions;

use App\Domain\Access\Enums\UserRole;
use App\Domain\Organization\Models\Organization;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class CreateOrganizationUserAction
{
    /**
     * @param  array{name: string, email: string, password: string, role: UserRole|string}  $data
     */
    public function handle(Organization $organization, array $data): User
    {
        return User::create([
            'organization_id' => $organization->id,
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role' => $data['role'],
        ]);
    }
}
