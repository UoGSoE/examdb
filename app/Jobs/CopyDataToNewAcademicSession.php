<?php

namespace App\Jobs;

use App\User;
use App\Course;
use App\Discipline;
use App\AcademicSession;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Support\Facades\DB;

class CopyDataToNewAcademicSession implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $session;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(AcademicSession $session)
    {
        $this->session = $session;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        DB::transaction(function () {
            Discipline::all()->each(fn ($discipline) => $this->replicateForNewSession($discipline)->save());

            User::all()->each(fn ($user) => $this->replicateForNewSession($user)->save());

            Course::with('discipline')->get()->each(function ($course) {
                $newDiscipline = new Discipline();
                if ($course->discipline_id) {
                    $newDiscipline = Discipline::where('title', '=', $course->discipline->title)
                                        ->where('academic_session_id', '=', $this->session->id)
                                        ->first();
                }
                $newCourse = $this->replicateForNewSession($course, ['discipline_id' => optional($newDiscipline)->id]);
                $newCourse->save();
            });
        });
    }

    protected function replicateForNewSession(Model $model, array $attribs = []): Model
    {
        return $model->replicate()->fill(array_merge($attribs, ['academic_session_id' => $this->session->id]));
    }
}
