<?php

namespace App\Domain\Organization\Actions;

use App\Domain\Organization\Models\Organization;

class CreateOrganizationAction
{
    public function handle(string $name): Organization
    {
        return Organization::create([
            'name' => $name,
        ]);
    }
}
