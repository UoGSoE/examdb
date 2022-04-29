<?php

namespace App\Providers;

use App\Models\AcademicSession;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class ViewServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        View::composer('layouts.navbar', function ($view) {
            $view->with('navbarAcademicSessions', cache()->rememberForever('navbarAcademicSessions', function () {
                return AcademicSession::orderBy('session')->get();
            }));
        });
    }
}
