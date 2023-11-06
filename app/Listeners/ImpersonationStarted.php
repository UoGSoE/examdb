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
     */
    public function handle(TakeImpersonation $event): void
    {
        activity()
            ->causedBy($event->impersonator)
            ->log(
                "Started impersonating {$event->impersonated->full_name}"
            );
    }
}
