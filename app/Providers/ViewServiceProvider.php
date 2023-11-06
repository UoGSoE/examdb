<?php

namespace App\Providers;

use App\Models\AcademicSession;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class ViewServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        View::composer('layouts.navbar', function ($view) {
            $view->with('navbarAcademicSessions', cache()->rememberForever('navbarAcademicSessions', function () {
                return AcademicSession::orderBy('session')->get();
            }));
        });
    }
}
