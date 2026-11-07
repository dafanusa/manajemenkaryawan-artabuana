<?php

namespace App\Providers;

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
        // Set Default Timezone and Locale (Waktu Indonesia Timur - WIT / UTC+9)
        date_default_timezone_set(config('app.timezone', 'Asia/Jayapura'));
        \Carbon\Carbon::setLocale(config('app.locale', 'id'));
    }
}
