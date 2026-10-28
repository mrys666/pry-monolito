<?php

namespace App\Domain\Organization\Actions;

use App\Domain\Organization\Models\Organization;

class UpdateOrganizationAction
{
    public function handle(Organization $organization, array $data): Organization
    {
        $organization->update([
            'name' => $data['name'],
            'tax_id' => $data['tax_id'] ?? null,
            'email' => $data['email'] ?? null,
            'phone' => $data['phone'] ?? null,
            'address' => $data['address'] ?? null,
        ]);

        return $organization;
    }
}
