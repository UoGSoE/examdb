<?php

namespace App\Listeners;

use Lab404\Impersonate\Events\LeaveImpersonation;

class ImpersonationStopped
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
     * @return void
     */
    public function handle(LeaveImpersonation $event)
    {
        activity()
            ->causedBy($event->impersonator)
            ->log(
                'Stopped impersonating '.$event->impersonated->full_name
            );
    }
}
