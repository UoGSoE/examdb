<?php

namespace App\Listeners;

use App\Events\PaperApproved;
use Illuminate\Support\Facades\Mail;
use App\Mail\NotifySetterAboutApproval;
use Illuminate\Queue\InteractsWithQueue;
use App\Mail\NotifyModeratorAboutApproval;
use Illuminate\Contracts\Queue\ShouldQueue;

class PaperWasApproved
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
     * @param  PaperApproved  $event
     * @return void
     */
    public function handle(PaperApproved $event)
    {
        activity()->causedBy($event->user)->log(
            "Approved {$event->category} paper for {$event->course->code}"
        );

        $event->course->setters->each(function ($setter) use ($event) {
            Mail::to($setter)->queue(new NotifySetterAboutApproval($event->course, $event->category, $event->user));
        });
    }
}
