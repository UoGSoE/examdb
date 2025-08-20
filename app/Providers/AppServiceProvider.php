<?php

namespace App\Providers;

use App\Models\AcademicSession;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Illuminate\Foundation\Console\AboutCommand;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Collection::macro('userLinks', function () {
            return $this->map(function ($user) {
                return '<a href="'.route('user.show', $user->id).'">'.$user->full_name.'</a>';
            });
        });

        AboutCommand::add("Academic Sessions", function () {
            foreach (AcademicSession::all() as $session) {
                $sessionOutput[$session->id] = ($session->is_default ? '(Default) ' : '') . $session->session;
            }
            return $sessionOutput;
        });

        Event::listen(function (\SocialiteProviders\Manager\SocialiteWasCalled $event) {
            $event->extendSocialite('keycloak', \SocialiteProviders\Keycloak\Provider::class);
        });
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
