<?php

namespace App\Listeners;

use Lab404\Impersonate\Events\TakeImpersonation;

class ImpersonationStarted
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
    public function handle(TakeImpersonation $event)
    {
        activity()
            ->causedBy($event->impersonator)
            ->log(
                "Started impersonating {$event->impersonated->full_name}"
            );
    }
}
