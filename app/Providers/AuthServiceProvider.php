<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        \App\Models\Course::class => \App\Policies\CoursePolicy::class,
        \App\Models\Paper::class => \App\Policies\PaperPolicy::class,
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
