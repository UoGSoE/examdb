<?php

namespace App\Jobs;

use App\Course;
use Carbon\Carbon;
use App\Discipline;
use RuntimeException;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Mail;
use Illuminate\Queue\SerializesModels;
use App\Mail\ExternalHasPapersToLookAt;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class NotifyExternals implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $disciplineTitle;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(string $disciplineTitle)
    {
        $this->disciplineTitle = $disciplineTitle;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if (! Discipline::all()->pluck('title')->contains($this->disciplineTitle)) {
            abort(500, 'Invalid disciplineTitle given : '.$this->disciplineTitle);
        }

        $discipline = Discipline::where('title', '=', $this->disciplineTitle)->first();
        $emails = Course::forDiscipline($discipline)
                    ->forSemester($this->getCurrentSemester())
                    ->externalsNotNotified()
                    ->whereHas('papers')
                    ->get()
                    ->map(function ($course) {
                        $course->markExternalNotified();

                        return $course->externals->pluck('email');
                    })
                    ->flatten()
                    ->unique();

        $emails->each(function ($email) {
            Mail::to($email)->later(now()->addSeconds(rand(1, 180)), new ExternalHasPapersToLookAt);
        });
    }

    protected function getCurrentSemester(): int
    {
        $startSemesterOne = Carbon::createFromFormat('Y-m-d', option('start_semester_1'));
        $startSemesterTwo = Carbon::createFromFormat('Y-m-d', option('start_semester_2'));
        $startSemesterThree = Carbon::createFromFormat('Y-m-d', option('start_semester_3'));

        if (now()->between($startSemesterOne, $startSemesterTwo)) {
            return 1;
        }

        if (now()->between($startSemesterTwo, $startSemesterThree)) {
            return 2;
        }

        if (now()->gte($startSemesterThree)) {
            return 3;
        }

        throw new RuntimeException('Could not figure out semester');
    }

    public function tags()
    {
        return [
            'tenant:' . tenant('id'),
            'discipline:' . $this->disciplineTitle,
        ];
    }
}
