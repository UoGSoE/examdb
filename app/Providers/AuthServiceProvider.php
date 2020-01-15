<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Blade;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        'App\Course' => 'App\Policies\CoursePolicy',
        'App\Paper' => 'App\Policies\PaperPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        Blade::if('admin', function () {
            return auth()->check() and auth()->user()->isAdmin();
        });

        Gate::define('download_registry', function ($user, $routeUser) {
            return $routeUser->is($user);
        });

        Gate::define('upload_paper', function ($user, $course) {
            return $user->isSetterFor($course) || $user->isModeratorFor($course) || $user->isExternalFor($course);
        });
    }
}
