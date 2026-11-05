<?php

namespace App\Support\Concerns;

use App\Domain\Inventory\Events\InventoryMovementRegistered;
use App\Models\User;

trait ListensForInventoryUpdates
{
    /**
     * @return array<string, string>
     */
    public function getListeners(): array
    {
        $user = auth()->user();
        $tenantId = ($user instanceof User ? $user->organization_id : null) ?? 'global';
        $eventAlias = InventoryMovementRegistered::BROADCAST_AS;

        return [
            "echo-private:organization.{$tenantId},.{$eventAlias}" => 'handleRealTimeInventoryUpdate',
        ];
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function handleRealTimeInventoryUpdate(array $payload = []): void
    {
        // Al ejecutarse este método vacío, Livewire 4 re-ejecuta with() y actualiza el DOM automáticamente.
    }
}
