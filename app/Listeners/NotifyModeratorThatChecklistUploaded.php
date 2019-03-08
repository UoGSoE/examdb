<?php

namespace App\Listeners;

use App\Paper;
use App\Events\PaperAdded;
use App\Mail\ChecklistUploaded;
use Illuminate\Support\Facades\Mail;
use App\Mail\NotifyModeratorAboutUpload;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class NotifyModeratorThatChecklistUploaded
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
        if ($event->paper->isntChecklist()) {
            return;
        }

        if ($event->user->isSetterFor($event->paper->course)) {
            $event->paper->course->moderators->each(function ($moderator) use ($event) {
                Mail::to($moderator)->queue(new ChecklistUploaded($event->paper));
            });
        }
    }
}
