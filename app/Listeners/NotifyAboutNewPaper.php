<?php

namespace App\Listeners;

use App\Events\PaperAdded;
use Illuminate\Support\Facades\Mail;
use App\Mail\NotifySetterAboutUpload;
use App\Mail\NotifyModeratorAboutUpload;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Mail\NotifyLocalsAboutExternalComments;

class NotifyAboutNewPaper
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
        // @TODO remove this class
        // bail out if the upload won't trigger any emails
        if ($event->paper->isntChecklist() and $event->paper->isntExternalComments()) {
            return;
        }
        if ($event->user->isSetterFor($event->paper->course)) {
            $event->paper->course->moderators->each(function ($moderator) use ($event) {
                Mail::to($moderator)->queue(new NotifyModeratorAboutUpload($event->paper));
            });
        }
        if ($event->user->isModeratorFor($event->paper->course)) {
            $event->paper->course->setters->each(function ($setter) use ($event) {
                Mail::to($setter)->queue(new NotifySetterAboutUpload($event->paper));
            });
        }
        if ($event->user->isExternalFor($event->paper->course)) {
            $event->paper->course->setters->each(function ($setter) use ($event) {
                Mail::to($setter)->queue(new NotifyLocalsAboutExternalComments($event->paper));
            });
            $event->paper->course->moderators->each(function ($moderator) use ($event) {
                Mail::to($moderator)->queue(new NotifyLocalsAboutExternalComments($event->paper));
            });
        }
    }
}
