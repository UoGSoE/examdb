<?php

namespace App\Console\Commands;

use App\Course;
use Illuminate\Console\Command;

class RemoveAllApprovalFlags extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'examdb:reset-all-approval-flags';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Resets all approval flags to false';

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
        ]));
    }
}
