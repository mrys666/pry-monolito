<?php

namespace App\Domain\Inventory\Jobs;

use App\Domain\Catalog\Models\Product;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

class SendLowStockAlertJob implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    // Configuración de reintentos y espera progresiva (Backoff)
    public int $tries = 3;
    public array $backoff = [5, 15, 30];
    public int $timeout = 60;

    // Evita encolar otra alerta para el mismo producto durante 1 hora (3600 seg)
    public int $uniqueFor = 3600;

    public function __construct(
        public readonly Product $product
    ) {}

    public function uniqueId(): string
    {
        return 'low-stock-alert-' . $this->product->id;
    }

    public function handle(): void
    {
        // Recargamos el producto por si fue reabastecido mientras el Job esperaba en la cola
        $product = $this->product->fresh();

        if (! $product || $product->stock > $product->minimum_stock) {
            return;
        }

        // Aquí podrías usar Notification::route('mail', 'admin@empresa.com')->notify(...)
        // Por ahora, registramos la alerta estructurada en el log del sistema:
        Log::warning('Alerta de Stock Bajo detectada.', [
            'product_id' => $product->id,
            'sku' => $product->sku,
            'name' => $product->name,
            'current_stock' => $product->stock,
            'minimum_stock' => $product->minimum_stock,
        ]);
    }

    public function failed(?Throwable $exception): void
    {
        Log::error('Fallo definitivo al procesar la alerta de stock bajo.', [
            'product_id' => $this->product->id,
            'exception' => $exception?->getMessage(),
        ]);
    }
}
