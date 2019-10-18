<?php

namespace App\Listeners;

use App\Paper;
use App\Events\PaperAdded;
use Illuminate\Support\Facades\Mail;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Mail\NotifySetterAboutModeratorComments;

class NotifySetterThatModeratorHasCommented
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
        if (!request()->user()->isModeratorFor($event->paper->course)) {
            return;
        }
        if ($event->paper->category == Paper::SECOND_RESIT_CATEGORY) {
            return;
        }

        $event->paper->course->setters->each(function ($setter) use ($event) {
            Mail::to($setter->email)->queue(new NotifySetterAboutModeratorComments($event->paper));
        });
    }
}
