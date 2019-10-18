<?php

namespace App\Jobs;

use App\Course;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Mail;
use Illuminate\Queue\SerializesModels;
use App\Mail\ExternalHasPapersToLookAt;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class NotifyExternals implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $validAreas = ['glasgow', 'uestc'];

    public $area;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(string $area)
    {
        $this->area = $area;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if (!in_array($this->area, $this->validAreas)) {
            abort(500, 'Invalid area given : ' . $this->area);
        }

        $emails = Course::forArea($this->area)
                    ->externalsNotNotified()
                    ->whereHas('papers')
                    ->get()
                    ->map(function ($course) {
                        $course->markExternalNotified();
                        return $course->externals->pluck('email');
                    })
                    ->flatten()
                    ->unique();

        $emails->each(function ($email) {
            Mail::to($email)->queue(new ExternalHasPapersToLookAt);
        });
    }
}
