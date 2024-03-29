<?php

namespace App\Console\Commands;

use App\User;
use App\Course;
use App\Discipline;
use App\AcademicSession;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Scopes\CurrentAcademicSessionScope;

class RetrofitAcademicSession extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'examdb:retrofit-academic-session {new_session}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Add an academic session to all courses, users and disciplines if its currently null';

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
        DB::transaction(function () {
            $newSession = AcademicSession::where('session', '=', $this->argument('new_session'))->firstOrFail();

            Course::withoutGlobalScope(CurrentAcademicSessionScope::class)->where('academic_session_id', '=', null)->get()->each(function ($course) use ($newSession) {
                $course->academic_session_id = $newSession->id;
                $course->save();
            });

            User::withoutGlobalScope(CurrentAcademicSessionScope::class)->where('academic_session_id', '=', null)->get()->each(function ($user) use ($newSession) {
                $user->academic_session_id = $newSession->id;
                $user->save();
            });

            Discipline::withoutGlobalScope(CurrentAcademicSessionScope::class)->where('academic_session_id', '=', null)->get()->each(function ($discipline) use ($newSession) {
                $discipline->academic_session_id = $newSession->id;
                $discipline->save();
            });
        });

        return 0;
    }
}
