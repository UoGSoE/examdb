<?php

namespace App\Console\Commands;

use App\Paper;
use App\Course;
use Carbon\Carbon;
use Illuminate\Console\Command;
use App\Mail\PaperworkIncomplete;
use Illuminate\Support\Facades\Mail;

class NotifyPaperworkIncomplete extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'exampapers:notify-paperwork-incomplete {type : "main" or "resit"}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Notify setters their paperwork is incomplete';

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
        if (!option_exists('externals_notification_date')) {
            abort(500, 'No externals_notification_date option set');
        }
        $deadline = Carbon::createFromFormat('Y-m-d', option('externals_notification_date'));
        if ($deadline->gt(now())) {
            return;
        }

        $setterEmails = Course::with('papers')->whereDoesntHave('papers', function ($query) {
            $query->where('subcategory', '=', 'Paper Checklist');
        })->get()->map(function ($course) {
            return $course->setters->pluck('email');
        })->flatten()->unique();

        $setterEmails->each(function ($email) {
            Mail::to($email)->queue(new PaperworkIncomplete);
        });
    }
}
