<?php

namespace App\Support\Concerns;

use App\Domain\Inventory\Events\InventoryMovementRegistered;

trait ListensForInventoryUpdates
{
    public function getListeners(): array
    {
        $tenantId = auth()->user()?->organization_id ?? 'global';
        $eventAlias = InventoryMovementRegistered::BROADCAST_AS;

        return [
            "echo-private:organization.{$tenantId},.{$eventAlias}" => 'handleRealTimeInventoryUpdate',
        ];
    }

    public function handleRealTimeInventoryUpdate(array $payload = []): void
    {
        // Al ejecutarse este método vacío, Livewire 4 re-ejecuta with() y actualiza el DOM automáticamente.
    }
}
