<?php

namespace App\Listeners;

use App\Models\Paper;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Mail\NotifySetterAboutPrintReadyPaper;

class NotifySetterThatPrintReadyPaperUploaded
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
     * @param  object  $event
     * @return void
     */
    public function handle($event)
    {
        if (! Str::startsWith($event->paper->subcategory, Paper::ADMIN_PRINT_READY_VERSION)) {
            return;
        }

        $event->paper->course->setters->pluck('email')
            ->unique()
            ->each(
                fn ($email) => Mail::to($email)->later(
                    now()->addSeconds(rand(1, 200)),
                    new NotifySetterAboutPrintReadyPaper($event->paper->course)
                )
            );
    }
}
