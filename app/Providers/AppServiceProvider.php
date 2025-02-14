<?php

namespace App\Providers;

use App\Models\AcademicSession;
use Illuminate\Foundation\Console\AboutCommand;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public const HOME = '/home';

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Collection::macro('userLinks', function () {
            return $this->map(function ($user) {
                return '<a href="'.route('user.show', $user->id).'">'.$user->full_name.'</a>';
            });
        });

        AboutCommand::add('Academic Sessions', function () {
            foreach (AcademicSession::all() as $session) {
                $sessionOutput[$session->id] = ($session->is_default ? '(Default) ' : '').$session->session;
            }

            return $sessionOutput;
        });

        $this->bootAuth();
    }

    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    public function bootAuth(): void
    {
        Blade::if('admin', function () {
            return auth()->check() and auth()->user()->isAdmin();
        });

        Gate::define('download_registry', function ($user, $routeUser) {
            return $routeUser->is($user);
        });

        Gate::define('upload_paper', function ($user, $course) {
            return $user->isAdmin() || $user->isSetterFor($course) || $user->isModeratorFor($course) || $user->isExternalFor($course);
        });
    }
}
