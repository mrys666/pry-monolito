<?php

use App\Domain\Access\Enums\UserRole;
use App\Models\User;

it('renderiza todas las paginas protegidas para un administrador', function (string $routeName) {
    $user = User::factory()->create(['role' => UserRole::Admin]);

    $this->actingAs($user)
        ->get(route($routeName))
        ->assertOk();
})->with([
    'dashboard',
    'catalog.index',
    'catalog.products.create',
    'catalog.categories.index',
    'catalog.categories.create',
    'inventory.index',
    'inventory.entry.create',
    'inventory.dispensation.create',
    'inventory.adjustment.create',
    'reports.index',
    'organization.users',
    'organization.settings',
]);
