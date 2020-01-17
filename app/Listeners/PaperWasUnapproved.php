<?php

namespace App\Listeners;

use App\Events\PaperUnapproved;
use Illuminate\Support\Facades\Mail;
use Illuminate\Queue\InteractsWithQueue;
use App\Mail\NotifySetterAboutUnapproval;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Mail\NotifyModeratorAboutUnapproval;

class PaperWasUnapproved
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
     * @param  PaperUnapproved  $event
     * @return void
     */
    public function handle(PaperUnapproved $event)
    {
        activity()->causedBy($event->user)->log(
            "Unapproved {$event->category} paper for {$event->course->code}"
        );

        $event->course->setters->each(function ($setter) use ($event) {
            Mail::to($setter)->queue(new NotifySetterAboutUnapproval($event->course, $event->category));
        });
    }
}
