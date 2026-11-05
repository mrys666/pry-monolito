<?php

namespace App\Domain\Organization\Exceptions;

use Exception;
use Illuminate\Contracts\Debug\ShouldntReport;

class UserHasInventoryMovementsException extends Exception implements ShouldntReport
{
    public function __construct(public readonly int $movementsCount)
    {
        parent::__construct(
            "No se puede eliminar este usuario porque tiene {$movementsCount} movimiento(s) de inventario registrado(s). Conserva el historial para mantener la auditoría."
        );
    }
}
