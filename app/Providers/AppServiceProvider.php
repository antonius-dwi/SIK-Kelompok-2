<?php

namespace App\Providers;

use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\ViewErrorBag;

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
        // Di request web, $errors di-share middleware ShareErrorsFromSession (menimpa nilai ini).
        // Default kosong ini menjaga view yang memakai @error / $errors tetap bisa dirender
        // di luar request web (CLI, render snapshot, halaman error) tanpa fatal error.
        View::share('errors', new ViewErrorBag);
    }
}
