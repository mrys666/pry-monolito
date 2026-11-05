<?php

namespace App\Domain\Organization\Actions;

use App\Domain\Inventory\Models\InventoryMovement;
use App\Domain\Organization\Exceptions\UserHasInventoryMovementsException;
use App\Models\User;

class DeleteOrganizationUserAction
{
    /**
     * @throws UserHasInventoryMovementsException
     */
    public function handle(User $user): void
    {
        $movementsCount = InventoryMovement::where('user_id', $user->id)->count();

        if ($movementsCount > 0) {
            throw new UserHasInventoryMovementsException($movementsCount);
        }

        $user->delete();
    }
}
