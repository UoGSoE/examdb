<?php

namespace App\Providers;

use App\Models\AcademicSession;
use Illuminate\Foundation\Console\AboutCommand;
use Illuminate\Support\Collection;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
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
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register(): void
    {
        //
    }
}
