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
     */
    public function handle(LeaveImpersonation $event): void
    {
        activity()
            ->causedBy($event->impersonator)
            ->log(
                'Stopped impersonating '.$event->impersonated->full_name
            );
    }
}
