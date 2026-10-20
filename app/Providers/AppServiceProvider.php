<?php

namespace App\Providers;

use App\Models\Product;
use App\Models\Quote;
use App\Models\User;
use App\Policies\ProductPolicy;
use App\Policies\QuotePolicy;
use App\Policies\UserPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

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
        Gate::policy(Product::class, ProductPolicy::class);
        Gate::policy(Quote::class, QuotePolicy::class);
        Gate::policy(User::class, UserPolicy::class);

        Gate::before(function (User $user, string $ability) {
            if (! $user->is_active) {
                return false;
            }

            if ($user->isAdmin()) {
                return true;
            }

            if (str_contains($ability, '.')) {
                return $user->hasPermission($ability);
            }

            return null;
        });
    }
}
