<?php

namespace App\Providers;

use App\Http\View\Composers\AdminHomeComposer;
use App\Http\View\Composers\OperarioLayoutComposer;
use App\Models\Galpon;
use App\Policies\GalponPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

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
        Gate::policy(Galpon::class, GalponPolicy::class);

        View::composer('pages.admin.home', AdminHomeComposer::class);
        View::composer('components.layouts.operario-mobile', OperarioLayoutComposer::class);

        Password::defaults(function () {
            return Password::min(8)
                ->letters()
                ->mixedCase()
                ->numbers();
        });
    }
}
