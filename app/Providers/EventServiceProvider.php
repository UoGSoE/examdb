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
        'App\Events\PaperAdded' => [
            'App\Listeners\NotifyModeratorThatChecklistUploaded',
            'App\Listeners\NotifySetterThatModeratorHasCommented',
            'App\Listeners\NotifySetterThatExternalHasCommented',
            'App\Listeners\NotifyTeachingOfficeThatExternalHasCommented',
            'App\Listeners\NotifyTechingOfficePaperForRegistryUploaded',
            'App\Listeners\LogThatPaperWasAdded',
        ],
        'Illuminate\Auth\Events\Login' => [
            'App\Listeners\UserLoggedIn',
        ],
        'Illuminate\Auth\Events\Attempting' => [
            'App\Listeners\DispachPasswordChecker',
        ],
        'App\Events\PaperApproved' => [
            'App\Listeners\PaperWasApproved',
        ],
        'App\Events\PaperUnapproved' => [
            'App\Listeners\PaperWasUnapproved',
        ],
        'App\Events\WlmImportComplete' => [
            'App\Listeners\NotifyUserWlmImportFinished',
        ],
        'Lab404\Impersonate\Events\TakeImpersonation' => [
            'App\Listeners\ImpersonationStarted',
        ],
        'Lab404\Impersonate\Events\LeaveImpersonation' => [
            'App\Listeners\ImpersonationStopped',
        ],
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();

        //
    }
}
