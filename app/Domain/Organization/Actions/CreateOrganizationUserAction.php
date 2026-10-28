<?php

namespace App\Domain\Organization\Actions;

use App\Models\User;
use App\Domain\Organization\Models\Organization;
use Illuminate\Support\Facades\Hash;

class CreateOrganizationUserAction
{
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
