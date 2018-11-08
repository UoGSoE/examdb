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

        if ($event->user->isSetterFor($event->course)) {
            $event->course->moderators->each(function ($moderator) use ($event) {
                Mail::to($moderator)->queue(new NotifyModeratorAboutApproval($event->course, $event->category));
            });
            return;
        }
        if ($event->user->isModeratorFor($event->course)) {
            $event->course->setters->each(function ($setter) use ($event) {
                Mail::to($setter)->queue(new NotifySetterAboutApproval($event->course, $event->category));
            });
            return;
        }
    }
}
