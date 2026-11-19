<?php

namespace App\Providers;

use App\Core\Tenancy\CurrentTenant;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(CurrentTenant::class);
    }

    public function boot(): void
    {
        //
    }
}
