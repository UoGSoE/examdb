<?php

namespace App\Jobs;

use App\User;
use App\Course;
use App\Discipline;
use App\AcademicSession;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Queue\SerializesModels;
use App\Mail\DataWasCopiedToNewSession;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Contracts\Queue\ShouldBeUnique;

class CopyDataToNewAcademicSession implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $sourceSession;
    public $targetSession;
    public $user;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(AcademicSession $sourceSession, AcademicSession $targetSession, User $user)
    {
        $this->sourceSession = $sourceSession;
        $this->targetSession = $targetSession;
        $this->user = $user;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        DB::transaction(function () {
            Discipline::where('academic_session_id', '=', $this->sourceSession->id)->get()->each(fn ($discipline) => $this->replicateForNewSession($discipline)->save());

            User::where('academic_session_id', '=', $this->sourceSession->id)->get()->each(fn ($user) => $this->replicateForNewSession($user)->save());

            Course::where('academic_session_id', '=', $this->sourceSession->id)->with('discipline')->get()->each(function ($course) {
                $newDiscipline = new Discipline();
                if ($course->discipline_id) {
                    $newDiscipline = Discipline::where('title', '=', $course->discipline->title)
                                        ->where('academic_session_id', '=', $this->targetSession->id)
                                        ->first();
                }
                $newCourse = $this->replicateForNewSession($course, ['discipline_id' => optional($newDiscipline)->id]);
                $newCourse->save();
                $course->setters->each(function ($setter) use ($newCourse) {
                    $newSetter = User::where('username', '=', $setter->username)->where('academic_session_id', '=', $this->targetSession->id)->first();
                    $newSetter->markAsSetter($newCourse);
                });
                $course->moderators->each(function ($moderator) use ($newCourse) {
                    $newModerator = User::where('username', '=', $moderator->username)->where('academic_session_id', '=', $this->targetSession->id)->first();
                    $newModerator->markAsModerator($newCourse);
                });
                $course->externals->each(function ($external) use ($newCourse) {
                    $newExternal = User::where('username', '=', $external->username)->where('academic_session_id', '=', $this->targetSession->id)->first();
                    $newExternal->markAsExternal($newCourse);
                });
            });

            Mail::to($this->user)->queue(new DataWasCopiedToNewSession($this->targetSession));
        });
    }

    protected function replicateForNewSession(Model $model, array $attribs = []): Model
    {
        return $model->replicate()->fill(array_merge($attribs, ['academic_session_id' => $this->targetSession->id]));
    }
}
