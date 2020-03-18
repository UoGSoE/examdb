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
                $course = $this->courseFromWlm($wlmCourse);
                $this->courseList[$course->code] = $course;
                $this->staffFromWlm($wlmCourse, false);
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

    protected function staffFromWlm($wlmCourse, $setterFlag = false)
    {
        if (! array_key_exists('Staff', $wlmCourse)) {
            return collect([]);
        }
        $staff = [];
        $staffIds = collect($wlmCourse['Staff'])->map(function ($wlmStaff) {
            if (! $this->staffList->has($wlmStaff['GUID'])) {
                $wlmStaff['Email'] = $this->getStaffEmail($wlmStaff);
                $this->staffList[$wlmStaff['GUID']] = User::staffFromWlmData($wlmStaff);
            }

            return $this->staffList[$wlmStaff['GUID']];
        })->pluck('id');
        foreach ($staffIds as $id) {
            $staff[$id] = ['is_setter' => $setterFlag];
        }

        return $staff;
    }

    protected function getStaffEmail($wlmStaff)
    {
        usleep($this->apiDelay);
        $staff = $this->client->getStaff($wlmStaff['GUID']);
        if (! preg_match('/\@/', $staff['Email'])) {
            $staff['Email'] = $wlmStaff['GUID'].'@glasgow.ac.uk';
        }

        return $staff['Email'];
    }
}
