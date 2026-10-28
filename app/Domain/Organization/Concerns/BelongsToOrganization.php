<?php

namespace App\Domain\Organization\Concerns;

use App\Domain\Organization\Models\Organization;
use App\Domain\Organization\Scopes\OrganizationScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Context;

/**
 * @mixin \Illuminate\Database\Eloquent\Model
 * @method static void addGlobalScope($scope, $implementation = null)
 * @method static void creating(callable|string $callback)
 */
trait BelongsToOrganization
{
    public static function bootBelongsToOrganization(): void
    {
        static::addGlobalScope(new OrganizationScope());

        static::creating(function (Model $model) {
            if (! $model->organization_id) {
                $model->organization_id = Context::get('organization_id') ?? Auth::user()?->organization_id;
            }
        });
    }

    public function organization(): BelongsTo
    {
        /** @var \Illuminate\Database\Eloquent\Model $this */
        return $this->belongsTo(Organization::class);
    }
}
