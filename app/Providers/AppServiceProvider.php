<?php

namespace App\Providers;

use App\Http\View\Composers\AdminHomeComposer;
use App\Http\View\Composers\AdminLayoutComposer;
use App\Http\View\Composers\OperarioLayoutComposer;
use App\Models\Galpon;
use App\Models\Lote;
use App\Models\User;
use App\Policies\GalponPolicy;
use App\Policies\LotePolicy;
use App\Policies\UserPolicy;
use App\Services\OperarioGalponResumenService;
use App\Services\OperarioGalponService;
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
        $this->app->scoped(OperarioGalponService::class);
        $this->app->scoped(OperarioGalponResumenService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::policy(Galpon::class, GalponPolicy::class);
        Gate::policy(Lote::class, LotePolicy::class);
        Gate::policy(User::class, UserPolicy::class);

        View::composer('pages.admin.home', AdminHomeComposer::class);
        View::composer('components.layouts.admin', AdminLayoutComposer::class);
        View::composer('components.layouts.operario-mobile', OperarioLayoutComposer::class);

        Password::defaults(function () {
            return Password::min(8)
                ->letters()
                ->mixedCase()
                ->numbers();
        });
    }
}
