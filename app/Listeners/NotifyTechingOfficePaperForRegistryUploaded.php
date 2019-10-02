<?php

namespace App\Listeners;

use App\Events\PaperAdded;
use App\Mail\NotifyTeachingOffice;
use Illuminate\Support\Facades\Mail;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class NotifyTechingOfficePaperForRegistryUploaded
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
     * @param  PaperAdded  $event
     * @return void
     */
    public function handle(PaperAdded $event)
    {
        if ($event->paper->subcategory != 'Paper For Registry') {
            return;
        }

        if (!option_exists('teaching_office_contact')) {
            // @TODO something better...
            abort(500);
        }
        Mail::to(option('teaching_office_contact'))->queue(new NotifyTeachingOffice($event->paper->course));
    }
}
