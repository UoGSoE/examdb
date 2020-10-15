<?php

namespace App\Wlm;

use App\Course;
use App\Mail\WlmImportProblem;
use App\User;
use App\Wlm\WlmClientInterface;
use Illuminate\Support\Facades\Mail;

class WlmImporter
{
    protected $client;
    protected $staffList;
    protected $studentList;
    protected $courseList;
    protected $apiDelay = 1000000 / 10; // delay between api requests in millionths of a second
    protected $course;

    public function __construct(WlmClientInterface $client)
    {
        $this->client = $client;
        $this->staffList = collect([]);
        $this->courseList = collect([]);
    }

    public function run($maximumCourses = 1000000)
    {
        try {
            $courses = $this->client->getCourses();
            if ($this->client->statusCode != 200) {
                throw new \Exception('Failed to get data from the WLM');
            }
            $courses->filter(function ($wlmCourse) {
                if (! preg_match('/^(ENG|UESTC|SIT|TEST)/', $wlmCourse['Code'])) {
                    return false;
                }

                return true;
            })->take($maximumCourses)->each(function ($wlmCourse) {
                $this->course = $this->courseFromWlm($wlmCourse);
                $this->courseList[$this->course->code] = $this->course;
                $this->settersFromWlm($wlmCourse);
                $this->moderatorsFromWlm($wlmCourse);
                // $this->externalsFromWlm($wlmCourse);
            });
        } catch (\Exception $e) {
            Mail::to(config('exampapers.sysadmin_email'))->send(new WlmImportProblem($e->getMessage()));
            throw $e;
        }

        return true;
    }

    protected function courseFromWlm($wlmCourse)
    {
        usleep($this->apiDelay);

        return Course::fromWlmData($wlmCourse);
    }

    protected function getStaffFromWlmCourse($wlmCourse, $key)
    {
        if (! array_key_exists('Exam', $wlmCourse)) {
            return collect([]);
        }
        if (! array_key_exists($key, $wlmCourse['Exam'])) {
            return collect([]);
        }
        $staff = [];

        return collect($wlmCourse['Exam'][$key])->map(function ($wlmStaff) {
            if (! $this->staffList->has($wlmStaff['GUID'])) {
                $wlmStaff['Email'] = $this->getStaffEmail($wlmStaff);
                $this->staffList[$wlmStaff['GUID']] = User::staffFromWlmData($wlmStaff);
            }

            return $this->staffList[$wlmStaff['GUID']];
        });
    }

    protected function settersFromWlm($wlmCourse, $setterFlag = false)
    {
        $setters = $this->getStaffFromWlmCourse($wlmCourse, 'Setters');
        $setters->each->markAsSetter($this->course);
    }

    protected function moderatorsFromWlm($wlmCourse, $setterFlag = false)
    {
        $moderators = $this->getStaffFromWlmCourse($wlmCourse, 'Moderators');
        $moderators->each->markAsModerator($this->course);
    }

    protected function externalsFromWlm($wlmCourse, $setterFlag = false)
    {
        $externals = $this->getStaffFromWlmCourse($wlmCourse, 'Externals');
        $externals->each->markAsExternal($this->course);
        $externals->each(function ($external) {
            $external->update([
                'email' => 'CHANGEME'.$external->id.'@glasgow.ac.uk',
                'username' => 'CHANGEME'.$external->id.'@glasgow.ac.uk',
                'is_external' => true,
            ]);
        });
    }

    protected function getStaffEmail($wlmStaff)
    {
        usleep($this->apiDelay);
        $staff = $this->client->getStaff($wlmStaff['GUID']);
        if ((! isset($staff['Email'])) or (! preg_match('/\@/', $staff['Email']))) {
            $staff['Email'] = $wlmStaff['GUID'].'@glasgow.ac.uk';
        }

        return $staff['Email'];
    }
}
