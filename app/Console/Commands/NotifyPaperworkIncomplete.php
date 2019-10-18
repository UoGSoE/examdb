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
    protected $signature = 'exampapers:notify-paperwork-incomplete {--area= : glasgow or uestc}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Notify staff their papers arent complete';

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
        $deadlineField = "internal_deadline_{$area}";
        if (!option_exists($deadlineField)) {
            abort(500, "No {$deadlineField} option set");
        }
        // check if it is one week before or one day after the deadline - otherwise we don't send emails
        $deadline = Carbon::createFromFormat('Y-m-d', option($deadlineField))->format('d/m/Y');
        if ($this->isntADayToSendAlerts($deadline)) {
            return;
        }

        $peopleToContact = Course::forArea($area)->with('staff')->get()->filter(function ($course) {
            return $course->isntFullyApproved();
        })->map(function ($course) {
            return $course->staff->filter(function ($staffMember) use ($course) {
                if ($staffMember->isExternalFor($course)) {
                    return false;
                }
                if ($course->isApprovedBy($staffMember, 'main') and $course->isApprovedBy($staffMember, 'resit')) {
                    return false;
                }
                return true;
            })->pluck('email');
        })->flatten()->unique();

        $peopleToContact->each(function ($email) {
            Mail::to($email)->queue(new PaperworkIncomplete);
        });
    }

    public function isntADayToSendAlerts($deadline)
    {
        return ($deadline != now()->addWeeks(1)->format('d/m/Y')) && ($deadline != now()->subDays(1)->format('d/m/Y'));
    }
}
