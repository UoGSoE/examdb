<?php

namespace App\Listeners;

use App\Sysadmin;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

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
     *
     * @param  object  $event
     * @return void
     */
    public function handle($event)
    {
        // TODO sysadmin activity log needs created
        $class = '\App\Sysadmin';
        if (auth()->guard('sysadmin')->user() instanceof $class) {
            return;
        }
        activity()->causedBy(auth()->user())->log('Logged in from IP '.request()->ip());
    }
}
