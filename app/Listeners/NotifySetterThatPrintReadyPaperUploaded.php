<?php

namespace App\Listeners;

use App\Mail\NotifySetterAboutPrintReadyPaper;
use App\Models\Paper;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

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
     */
    public function handle(object $event): void
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
