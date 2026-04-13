<?php

namespace App\Providers;

use App\Models\FutsalMatch;
use App\Models\Team;
use App\Observers\FutsalMatchObserver;
use App\Observers\TeamObserver;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Team::observe(TeamObserver::class);
        FutsalMatch::observe(FutsalMatchObserver::class);

        // Auditor hanya boleh akses resource audit
        Gate::define('manage-resources', fn ($user) => $user->role === 'admin');
        Gate::define('audit-resources',  fn ($user) => $user->role === 'auditor');
    }
}
