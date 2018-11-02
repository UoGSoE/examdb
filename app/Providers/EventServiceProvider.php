<?php

namespace App\Providers;

use Illuminate\Support\Facades\Event;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        'App\Events\PaperAdded' => [
            'App\Listeners\NotifyAboutNewPaper',
            'App\Listeners\LogThatPaperWasAdded',
        ],
        'Illuminate\Auth\Events\Login' => [
            'App\Listeners\UserLoggedIn'
        ],
        'App\Events\PaperApproved' => [
            'App\Listeners\PaperWasApproved'
        ],
        'App\Events\PaperUnapproved' => [
            'App\Listeners\PaperWasUnapproved'
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
