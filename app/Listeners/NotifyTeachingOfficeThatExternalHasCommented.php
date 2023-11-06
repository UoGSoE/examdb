<?php

namespace App\Listeners;

use App\Events\PaperAdded;
use App\Mail\NotifyTeachingOfficeExternalHasCommented;
use Illuminate\Support\Facades\Mail;

class NotifyTeachingOfficeThatExternalHasCommented
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
    public function handle(PaperAdded $event): void
    {
        if (! request()->user()->isExternalFor($event->paper->course)) {
            return;
        }

        $contact = $event->paper->getDisciplineContact();

        if (! $contact) {
            // @TODO something better...
            abort(500, 'No contact email address found');
        }

        Mail::to($contact)->queue(new NotifyTeachingOfficeExternalHasCommented($event->paper->course));
    }
}
