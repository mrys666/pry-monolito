<?php

namespace App\Domain\Inventory\Events;

use App\Domain\Inventory\Models\InventoryMovement;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class InventoryMovementRegistered implements ShouldBroadcast, ShouldDispatchAfterCommit
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public const BROADCAST_AS = 'inventory.movement.registered';

    public function __construct(
        public readonly InventoryMovement $movement
    ) {}

    /**
     * Canal privado exclusivo de la organización a la que pertenece el movimiento.
     */
    public function broadcastOn(): array
    {
        $tenantId = $this->movement->organization_id ?? 'global';

        return [
            new PrivateChannel("organization.{$tenantId}"),
        ];
    }

    /**
     * Alias limpio del evento para escuchar desde Echo y Livewire.
     */
    public function broadcastAs(): string
    {
        return self::BROADCAST_AS;
    }

    /**
     * Enviamos solo datos escalares ligeros por el WebSocket, evitando exponer modelos completos.
     */
    public function broadcastWith(): array
    {
        return [
            'movement_id' => $this->movement->id,
            'product_id' => $this->movement->product_id,
            'type' => $this->movement->type,
            'quantity' => $this->movement->quantity,
            'stock_after' => $this->movement->stock_after,
        ];
    }
}
