<?php

namespace App\Listeners;

use App\Events\PaperUnapproved;
use App\Mail\NotifyModeratorAboutUnapproval;
use App\Mail\NotifySetterAboutUnapproval;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;

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
