<?php

namespace App\Jobs;

use Exception;
use App\Course;
use App\Discipline;
use App\User;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Validator;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Support\Facades\Redis;
use Illuminate\Validation\Rule;

class ImportCourseRow implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public array $spreadsheetRow = [];

    public int $rowNumber;

    public $errorSetName;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(array $spreadsheetRow, int $rowNumber)
    {
        $this->spreadsheetRow = $spreadsheetRow;
        $this->rowNumber = $rowNumber;
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
        $this->errorSetName = optional($this->batch())->id . '-errors';
        $row = [
            'code' => $this->spreadsheetRow[0],
            'title' => $this->spreadsheetRow[1],
            'discipline' => $this->spreadsheetRow[2],
            'semester' => $this->spreadsheetRow[3],
            'setters' => $this->spreadsheetRow[4] ?? '',
            'moderators' => $this->spreadsheetRow[5] ?? '',
            'is_examined' => $this->spreadsheetRow[6] ?? 'Y',
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

        $discipline = Discipline::firstOrCreate(['title' => $row['discipline']]);
        $course = Course::updateOrCreate(
            ['code' => $row['code']],
            [
                'title' => $row['title'],
                'semester' => $row['semester'],
                'discipline_id' => $discipline->id,
                'is_examined' => preg_match('/[yY]/', $row['is_examined']) === 1,
            ],
        );

        $course->setters()->sync([]);

        $setters = explode(',', $row['setters']);
        collect($setters)->each(function ($guid) use ($course) {
            $guid = trim(strtolower($guid));
            $user = User::where('username', '=', $guid)->first();
            if (! $user) {
                $ldapUser = \Ldap::findUser($guid);
                if (! $ldapUser) {
                    $this->addError('invalid GUID ' . $guid);
                    return;
                }
                $user = User::createFromLdap($ldapUser);
            }
            $user->markAsSetter($course);
        });

        $course->moderators()->sync([]);

        $moderators = explode(',', $row['moderators']);
        collect($moderators)->each(function ($guid) use ($course) {
            $guid = trim(strtolower($guid));
            $user = User::where('username', '=', $guid)->first();
            if (! $user) {
                $ldapUser = \Ldap::findUser($guid);
                if (! $ldapUser) {
                    $this->addError('invalid GUID ' . $guid);
                    return;
                }
                $user = User::createFromLdap($ldapUser);
            }
            $user->markAsModerator($course);
        });
    }

    protected function addError(string $message)
    {
        Redis::sadd($this->errorSetName, "Invalid data on row {$this->rowNumber} : " . $message);
    }
}
