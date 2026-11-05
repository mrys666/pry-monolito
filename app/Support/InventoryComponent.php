<?php

namespace App\Support;

use App\Support\Concerns\ListensForInventoryUpdates;
use Livewire\Component;

abstract class InventoryComponent extends Component
{
    use ListensForInventoryUpdates;
}
