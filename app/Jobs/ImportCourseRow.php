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

class ImportCourseRow implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public array $spreadsheetRow = [];
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(array $spreadsheetRow)
    {
        $this->spreadsheetRow = $spreadsheetRow;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $row = [
            'code' => $this->spreadsheetRow[0],
            'title' => $this->spreadsheetRow[1],
            'discipline' => $this->spreadsheetRow[2],
            'semester' => $this->spreadsheetRow[3],
            'setters' => $this->spreadsheetRow[4] ?? '',
            'moderators' => $this->spreadsheetRow[5] ?? '',
        ];
        $validator = Validator::make($row, [
            'code' => 'required|regex:/^[A-Z]+[0-9]+/i',
            'title' => 'required|string',
            'discipline' => 'required|string',
            'semester' => 'required|integer',
        ]);
        if ($validator->fails()) {
            $this->fail(new Exception('Invalid data on row : ' . implode(',', $validator->errors()->toArray())));
        }

        $discipline = Discipline::firstOrCreate(['title' => $row['discipline']]);
        $course = Course::updateOrCreate(
            ['code' => $row['code']],
            [
                'title' => $row['title'],
                'semester' => $row['semester'],
                'discipline_id' => $discipline->id,
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
                    // add to some sort of errorbag?
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
                    // add to some sort of errorbag?
                    return;
                }
                $user = User::createFromLdap($ldapUser);
            }
            $user->markAsModerator($course);
        });
    }
}
