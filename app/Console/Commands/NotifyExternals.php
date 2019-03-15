<?php

namespace App\Console\Commands;

use App\Paper;
use App\Course;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use App\Mail\ExternalHasPapersToLookAt;

class NotifyExternals extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'exampapers:notify-externals';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Notify externals about papers they need to look at';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        if (!option_exists('main_deadline')) {
            abort(500, "No 'main_deadline' option set");
        }

        $deadline = Carbon::createFromFormat('Y-m-d', option('main_deadline'));
        if ($deadline->gt(now())) {
            return;
        }

        Course::externalsNotNotified()->whereHas('papers')->get()->each(function ($course) {
            $course->externals->pluck('email')->flatten()->unique()->each(function ($email) {
                Mail::to($email)->queue(new ExternalHasPapersToLookAt);
            });
            $course->markExternalNotified();
        });
    }
}
