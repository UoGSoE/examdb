<?php

namespace App\Console\Commands;

use App\Models\Course;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use InvalidArgumentException;

#[Signature('examdb:duplicate-course {code} {newcode}')]
#[Description('Make a copy of an existing course but with a new course code')]
class DuplicateCourse extends Command
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
