<?php

namespace App\Console\Commands;

use App\Models\Course;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('examdb:reset-all-approval-flags')]
#[Description('Resets all approval flags to false')]
class RemoveAllApprovalFlags extends Command
{
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
     * @return int
     */
    public function handle()
    {
        Course::all()->each(fn ($course) => $course->update([
            'moderator_approved_main' => false,
            'moderator_approved_resit' => false,
            'external_approved_main' => false,
            'external_approved_resit' => false,
            'moderator_approved_assessment' => false,
            'external_approved_assessment' => false,
            'external_notified' => false,
        ]));
    }
}
