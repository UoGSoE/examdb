<?php

namespace App\Jobs;

use App\Tenant;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Contracts\Queue\ShouldBeUnique;

class BootstrapNewTenant implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tenant;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Tenant $tenant)
    {
        $this->tenant = $tenant;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->tenant->run(function ($tenant) {
            $baseDate = now()->addYears(3)->format('Y-m-d');
            option(['staff_submission_deadline' => $baseDate]);
            option(['date_receive_call_for_papers' => $baseDate]);
            option(['internal_moderation_deadline' => $baseDate]);
            option(['date_remind_office_externals' => $baseDate]);
            option(['external_moderation_deadline' => $baseDate]);
            option(['print_ready_deadline' => $baseDate]);
            option(['teaching_office_contact' => 'glasgow@example.com']);
            option(['start_semester_1' => now()->subMonths(1)->format('Y-m-d')]);
            option(['start_semester_2' => now()->addMonths(3)->format('Y-m-d')]);
            option(['start_semester_3' => now()->addMonths(9)->format('Y-m-d')]);
        });
    }
}
