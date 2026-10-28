<?php

namespace App\Providers;

use Illuminate\Database\Eloquent\Model;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

use App\Domain\Access\Policies\CategoryPolicy;
use App\Domain\Access\Policies\InventoryMovementPolicy;
use App\Domain\Access\Policies\ProductPolicy;
use App\Domain\Catalog\Models\Category;
use App\Domain\Catalog\Models\Product;
use App\Domain\Inventory\Models\InventoryMovement;
use Illuminate\Support\Facades\Gate;
use Livewire\Blaze\Blaze;

use App\Domain\Organization\Models\Organization;
use App\Domain\Organization\Policies\OrganizationPolicy;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Model::preventLazyLoading(! $this->app->isProduction());

        Blaze::optimize()->in(resource_path('views/components'));

        Gate::policy(Product::class, ProductPolicy::class);
        Gate::policy(InventoryMovement::class, InventoryMovementPolicy::class);
        Gate::policy(Organization::class, OrganizationPolicy::class);
        Gate::policy(Category::class, CategoryPolicy::class);
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(
            fn(): ?Password => app()->isProduction()
                ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
                : null,
        );
    }
}
