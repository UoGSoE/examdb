<?php

namespace App\Jobs;

use App\Models\Course;
use App\Models\Discipline;
use App\Models\User;
use App\Scopes\CurrentAcademicSessionScope;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class ImportCourseRow implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public array $spreadsheetRow = [];

    public int $rowNumber;

    public $errorSetName;

    public $academicSessionId;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(array $spreadsheetRow, int $rowNumber, int $academicSessionId)
    {
        $this->spreadsheetRow = $spreadsheetRow;
        $this->rowNumber = $rowNumber;
        $this->academicSessionId = $academicSessionId;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // optional() call is here purely for test code - when running the job directly in a test, there is no 'batch'.
        // See ImportCourseDataSpreadsheetTest::the_import_course_row_job_actually_creates_records_for_the_data for instance.
        $this->errorSetName = $this->batch()?->id.'-errors';

        if (count($this->spreadsheetRow) < 4) {
            $this->addError('Row is missing key data and is less than 4 columns');

            return;
        }

        $row = [
            'code' => $this->spreadsheetRow[0],
            'title' => $this->spreadsheetRow[1],
            'discipline' => $this->spreadsheetRow[2],
            'semester' => $this->spreadsheetRow[3],
            'setters' => $this->spreadsheetRow[4] ?? '',
            'moderators' => $this->spreadsheetRow[6] ?? '',
            'is_examined' => $this->spreadsheetRow[10] ?? 'Y',
        ];
        $validator = Validator::make($row, [
            'code' => 'required|regex:/^[A-Z]+[0-9]+/i',
            'title' => 'required|string',
            'discipline' => 'required|string',
            'semester' => 'required|integer',
            'is_examined' => ['required', Rule::in(['Y', 'y', 'N', 'n'])],
        ]);
        if ($validator->fails()) {
            $this->addError(implode(',', $validator->messages()->all()));

            return;
        }

        // $discipline = Discipline::firstOrCreate(['title' => $row['discipline'], 'academic_session_id' => $this->academicSessionId]);
        $discipline = Discipline::withoutGlobalScope(CurrentAcademicSessionScope::class)
            ->where('title', '=', $row['discipline'])
            ->where('academic_session_id', '=', $this->academicSessionId)
            ->first();
        if (! $discipline) {
            $discipline = Discipline::create(['title' => $row['discipline'], 'academic_session_id' => $this->academicSessionId]);
        }

        $course = Course::withoutGlobalScope(CurrentAcademicSessionScope::class)
            ->where('code', '=', $row['code'])
            ->where('academic_session_id', '=', $this->academicSessionId)
            ->first();
        if (! $course) {
            $course = new Course(['code' => $row['code'], 'academic_session_id' => $this->academicSessionId]);
        }
        $course->title = $row['title'];
        $course->discipline_id = $discipline->id;
        $course->semester = $row['semester'];
        $course->is_examined = preg_match('/[yY]/', $row['is_examined']) === 1;
        $course->save();

        // ['code' => $row['code']],
        // [
        //     'title' => $row['title'],
        //     'semester' => $row['semester'],
        //     'discipline_id' => $discipline->id,
        //     'is_examined' => preg_match('/[yY]/', $row['is_examined']) === 1,
        //     'academic_session_id' => $this->academicSessionId,
        // ],
        // );

        $course->setters()->sync([]);

        $setters = explode(',', $row['setters']);
        collect($setters)->each(function ($guid) use ($course) {
            $guid = trim(strtolower($guid));
            $user = User::withoutGlobalScope(CurrentAcademicSessionScope::class)
                ->where('username', '=', $guid)
                ->where('academic_session_id', '=', $this->academicSessionId)
                ->first();
            if (! $user) {
                $ldapUser = \Ldap::findUser($guid);
                if (! $ldapUser) {
                    $this->addError('invalid GUID '.$guid);

                    return;
                }
                $user = User::createFromLdap($ldapUser, $this->academicSessionId);
            }
            $user->markAsSetter($course);
        });

        $course->moderators()->sync([]);

        $moderators = explode(',', $row['moderators']);
        collect($moderators)->each(function ($guid) use ($course) {
            $guid = trim(strtolower($guid));
            $user = User::withoutGlobalScope(CurrentAcademicSessionScope::class)
                ->where('username', '=', $guid)
                ->where('academic_session_id', '=', $this->academicSessionId)
                ->first();
            if (! $user) {
                $ldapUser = \Ldap::findUser($guid);
                if (! $ldapUser) {
                    $this->addError('invalid GUID '.$guid);

                    return;
                }
                $user = User::createFromLdap($ldapUser, $this->academicSessionId);
            }
            $user->markAsModerator($course);
        });
    }

    protected function addError(string $message)
    {
        Redis::sadd($this->errorSetName, "Invalid data on row {$this->rowNumber} : ".$message);
    }
}
