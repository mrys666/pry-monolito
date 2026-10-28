<?php

namespace App\Domain\Inventory\Exceptions;

use Exception;
use Illuminate\Contracts\Debug\ShouldntReport;

class InsufficientStockException extends Exception implements ShouldntReport
{
    public function __construct(public readonly int $requested, public readonly int $available)
    {
        parent::__construct("No hay stock suficiente. Solicitado: {$requested}, Disponible: {$available}.");
    }
}
