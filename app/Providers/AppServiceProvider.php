<?php

namespace App\Providers;

use App\Models\Invite;
use App\Observers\InviteObserver;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;

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
        if(env('FORCE_HTTPS', false)) URL::forceScheme('https');

        // Observers
        Invite::observe(InviteObserver::class);
    }
}
