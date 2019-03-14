<?php

namespace App\Console\Commands;

use App\Paper;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use App\Mail\ExternalHasPapersToLookAt;
use Carbon\Carbon;

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
            abort(500, 'No main_deadline option set');
        }
        $deadline = Carbon::createFromFormat('Y-m-d', option('main_deadline'));
        if ($deadline->gt(now())) {
            return;
        }

        $externalEmails = Paper::with('course.externals')->readyForExternals()->get()->map(function ($paper) {
            return $paper->course->externals->pluck('email');
        })->flatten()->unique();

        $externalEmails->each(function ($email) {
            Mail::to($email)->queue(new ExternalHasPapersToLookAt);
        });
    }
}
