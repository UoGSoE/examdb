<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        \App\Events\PaperAdded::class => [
            \App\Listeners\NotifySetterThatExternalHasCommented::class,
            \App\Listeners\NotifyTeachingOfficeThatExternalHasCommented::class,
            \App\Listeners\NotifyTechingOfficePaperForRegistryUploaded::class,
            \App\Listeners\NotifySettersPaperForRegistryUploaded::class,
            \App\Listeners\LogThatPaperWasAdded::class,
        ],
        \App\Events\ChecklistUpdated::class => [
            \App\Listeners\NotifyStaffThatChecklistUpdated::class,
        ],
        'Illuminate\Auth\Events\Login' => [
            \App\Listeners\UserLoggedIn::class,
        ],
        'Illuminate\Auth\Events\Attempting' => [
            \App\Listeners\DispachPasswordChecker::class,
        ],
        \App\Events\PaperApproved::class => [
            \App\Listeners\PaperWasApproved::class,
        ],
        \App\Events\PaperUnapproved::class => [
            \App\Listeners\PaperWasUnapproved::class,
        ],
        'Lab404\Impersonate\Events\TakeImpersonation' => [
            \App\Listeners\ImpersonationStarted::class,
        ],
        'Lab404\Impersonate\Events\LeaveImpersonation' => [
            \App\Listeners\ImpersonationStopped::class,
        ],
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {

        //
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     *
     * @return bool
     */
    public function shouldDiscoverEvents()
    {
        return false;
    }
}

