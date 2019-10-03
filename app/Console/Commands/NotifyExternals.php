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
    protected $validAreas = [ 'glasgow', 'uestc' ];

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'exampapers:notify-externals {--area= : either glasgow or uestc}';

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
        $area = $this->option('area');

        if (!in_array($area, $this->validAreas)) {
            $this->error('Invalid area given : ' . $area);
            return;
        }

        $dateOption = "main_deadline_{$area}";
        if (!option_exists($dateOption)) {
            abort(500, "No 'main_deadline' option set for area {$area}");
        }

        $deadline = Carbon::createFromFormat('Y-m-d', option($dateOption));
        if ($deadline->gt(now())) {
            return;
        }

        $emails = Course::forArea($area)->externalsNotNotified()->whereHas('papers')->get()->map(function ($course) {
            $course->markExternalNotified();
            return $course->externals->pluck('email');
        })->flatten()->unique();

        $emails->each(function ($email) {
            Mail::to($email)->queue(new ExternalHasPapersToLookAt);
        });
    }
}
