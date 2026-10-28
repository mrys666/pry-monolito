<?php

use App\Models\User;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function (User $user, int $id): bool {
    return (int) $user->id === (int) $id;
});

/**
 * Canal privado aislado por organización (Multi-tenant).
 * Si el proyecto aún opera con usuarios sin empresa asignada, permite el canal 'global'.
 */
Broadcast::channel('organization.{organizationId}', function (User $user, string $organizationId): bool {
    $userOrgId = (string) ($user->organization_id ?? 'global');

    return $userOrgId === $organizationId;
});
