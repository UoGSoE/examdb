<?php

namespace App\Providers;

use App\Events\ChecklistUpdated;
use App\Events\PaperAdded;
use App\Events\PaperApproved;
use App\Events\PaperUnapproved;
use App\Listeners\DispachPasswordChecker;
use App\Listeners\ImpersonationStarted;
use App\Listeners\ImpersonationStopped;
use App\Listeners\LogThatPaperWasAdded;
use App\Listeners\NotifySettersPaperForRegistryUploaded;
use App\Listeners\NotifySetterThatExternalHasCommented;
use App\Listeners\NotifySetterThatPrintReadyPaperUploaded;
use App\Listeners\NotifyStaffThatChecklistUpdated;
use App\Listeners\NotifyTeachingOfficeThatExternalHasCommented;
use App\Listeners\NotifyTechingOfficePaperForRegistryUploaded;
use App\Listeners\PaperWasApproved;
use App\Listeners\PaperWasUnapproved;
use App\Listeners\UserLoggedIn;
use App\Models\AcademicSession;
use Illuminate\Auth\Events\Attempting;
use Illuminate\Auth\Events\Login;
use Illuminate\Foundation\Console\AboutCommand;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Lab404\Impersonate\Events\LeaveImpersonation;
use Lab404\Impersonate\Events\TakeImpersonation;

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

        Event::listen(PaperAdded::class, NotifySetterThatExternalHasCommented::class);
        Event::listen(PaperAdded::class, NotifyTeachingOfficeThatExternalHasCommented::class);
        Event::listen(PaperAdded::class, NotifyTechingOfficePaperForRegistryUploaded::class);
        Event::listen(PaperAdded::class, NotifySettersPaperForRegistryUploaded::class);
        Event::listen(PaperAdded::class, LogThatPaperWasAdded::class);
        Event::listen(PaperAdded::class, NotifySetterThatPrintReadyPaperUploaded::class);

        Event::listen(ChecklistUpdated::class, NotifyStaffThatChecklistUpdated::class);

        Event::listen(Login::class, UserLoggedIn::class);

        Event::listen(Attempting::class, DispachPasswordChecker::class);

        Event::listen(PaperApproved::class, PaperWasApproved::class);

        Event::listen(PaperUnapproved::class, PaperWasUnapproved::class);

        Event::listen(TakeImpersonation::class, ImpersonationStarted::class);

        Event::listen(LeaveImpersonation::class, ImpersonationStopped::class);

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
