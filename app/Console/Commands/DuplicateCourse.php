<?php

namespace App\Console\Commands;

use App\Course;
use Illuminate\Console\Command;
use InvalidArgumentException;

class DuplicateCourse extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'examdb:duplicate-course {code} {newcode}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Make a copy of an existing course but with a new course code';

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
        $existingCode = strtoupper($this->argument('code'));
        $newCode = strtoupper($this->argument('newcode'));

        $course = Course::findByCode($existingCode);
        if (! $course) {
            $this->error("Could not find course {$existingCode}");

            return 1;
        }

        try {
            $newCourse = $course->createDuplicate($newCode);
        } catch (InvalidArgumentException $e) {
            $this->error($e->getMessage());

            return 1;
        }

        $this->info("New course {$newCode} created with ID {$newCourse->id}.");

        return 0;
    }
}
