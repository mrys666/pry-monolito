<?php

namespace App\Domain\Reports\Listeners;

use App\Domain\Inventory\Events\InventoryMovementRegistered;
use App\Domain\Reports\Queries\InventorySummaryQuery;

class InvalidateReportCacheListener
{
    public function handle(InventoryMovementRegistered $event): void
    {
        InventorySummaryQuery::bumpVersionForTenant($event->movement->organization_id);
    }
}
