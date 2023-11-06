<?php

namespace App\Listeners;

class UserLoggedIn
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(object $event): void
    {
        activity()->causedBy(auth()->user())->log('Logged in from IP '.request()->ip());
    }
}
