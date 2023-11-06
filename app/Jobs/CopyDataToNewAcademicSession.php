<?php

namespace App\Jobs;

use App\Mail\DataWasCopiedToNewSession;
use App\Models\AcademicSession;
use App\Models\Course;
use App\Models\Discipline;
use App\Models\User;
use App\Scopes\CurrentAcademicSessionScope;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

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
     */
    public function handle(): void
    {
        DB::transaction(function () {
            Discipline::withoutGlobalScope(CurrentAcademicSessionScope::class)->where('academic_session_id', '=', $this->sourceSession->id)->get()->each(fn ($discipline) => $this->replicateForNewSession($discipline)->save());

            User::withTrashed()->withoutGlobalScope(CurrentAcademicSessionScope::class)->where('academic_session_id', '=', $this->sourceSession->id)->get()->each(fn ($user) => $this->replicateForNewSession($user)->save());

            Course::withTrashed()->withoutGlobalScope(CurrentAcademicSessionScope::class)->where('academic_session_id', '=', $this->sourceSession->id)->get()->each(function ($course) {
                $newDiscipline = new Discipline();
                if ($course->discipline_id) {
                    $newDiscipline = Discipline::withoutGlobalScope(CurrentAcademicSessionScope::class)->where('title', '=', $course->discipline->title)
                        ->where('academic_session_id', '=', $this->targetSession->id)
                        ->first();
                }
                $newCourse = $this->replicateForNewSession($course, ['discipline_id' => $newDiscipline?->id]);
                foreach ($newCourse->flagsToClearOnDuplication as $flag) {
                    $newCourse->$flag = false;
                }
                $newCourse->save();
                $course->setters->each(function ($setter) use ($newCourse) {
                    $newSetter = User::withoutGlobalScope(CurrentAcademicSessionScope::class)->where('username', '=', $setter->username)->where('academic_session_id', '=', $this->targetSession->id)->first();
                    $newSetter->markAsSetter($newCourse);
                });
                $course->moderators->each(function ($moderator) use ($newCourse) {
                    $newModerator = User::withoutGlobalScope(CurrentAcademicSessionScope::class)->where('username', '=', $moderator->username)->where('academic_session_id', '=', $this->targetSession->id)->first();
                    $newModerator->markAsModerator($newCourse);
                });
                $course->externals->each(function ($external) use ($newCourse) {
                    $newExternal = User::withoutGlobalScope(CurrentAcademicSessionScope::class)->where('username', '=', $external->username)->where('academic_session_id', '=', $this->targetSession->id)->first();
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
