<?php

namespace App\Listeners;

use App\Events\ChecklistUpdated as EventsChecklistUpdated;
use App\Events\PaperAdded;
use App\Mail\ChecklistUpdated;
use App\Mail\ChecklistUploaded;
use App\Mail\NotifyModeratorAboutUpload;
use App\Paper;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;

class NotifyStaffThatChecklistUpdated
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
     * @param  EventsChecklistUpdated  $event
     * @return void
     */
    public function handle(EventsChecklistUpdated $event)
    {
        if ($event->checklist->category == Paper::SECOND_RESIT_CATEGORY) {
            return;
        }

        if ($event->checklist->user->isSetterFor($event->checklist->course)) {
            $event->checklist->course->moderators->each(function ($moderator) use ($event) {
                Mail::to($moderator)->queue(new ChecklistUpdated($event->checklist));
            });
        }
        if ($event->checklist->user->isModeratorFor($event->checklist->course)) {
            $event->checklist->course->setters->each(function ($setter) use ($event) {
                Mail::to($setter)->queue(new ChecklistUpdated($event->checklist));
            });
        }
    }
}
