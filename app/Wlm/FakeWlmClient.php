<?php

namespace App\Wlm;

class FakeWlmClient implements WlmClientInterface
{
    public $response;
    public $statusCode;
    protected $wlmStaff;
    public $responseCode;
    public $responseMessage;

    public function __construct()
    {
        $this->wlmStaff = collect([]);
    }

    public function getData($url)
    {
        return collect([]);
    }

    public function getCourses()
    {
        $this->statusCode = 200;

        return collect(['TEST1234' => $this->getCourse1(), 'TEST4321' => $this->getCourse2()]);
    }

    public function getCourse($code)
    {
        $this->statusCode = 200;

        return $this->getCourse1();
    }

    public function getStaff($guid)
    {
        $this->statusCode = 200;
        if ($guid == 'NONEXISTANT') {
            $this->responseCode = -1;
            $this->responseMessage = 'No such GUID';

            return collect([]);
        }
        if ($guid == 'WLMDOWN') {
            throw new \Exception('WLM Error');
        }
        if (! $this->wlmStaff->has($guid)) {
            $this->wlmStaff[$guid] = collect([
                'GUID' => $guid,
                'Email' => "{$guid}@glasgow.ac.uk",
                'Surname' => 'McFake',
                'Forenames' => 'Jake',
            ]);
        }

        return $this->wlmStaff[$guid];
    }

    protected function getCourse1()
    {
        return [
            'Code' => 'TEST1234',
            'Title' => 'Fake Course 1234',
            'ActiveFlag' => 'Yes',
            'Discipline' => 'Discipline the first',
            'Exam' => [
                'Setters' => [
                    'fake1x' => [
                        'GUID' => 'fake1x',
                        'Surname' => 'Faker',
                        'Forenames' => 'Prof',
                    ],
                    'blah2y' => [
                        'GUID' => 'blah2y',
                        'Surname' => 'McManus',
                        'Forenames' => 'Mark',
                    ],
                ],
                'Moderators' => [
                    'fake2x' => [
                        'GUID' => 'fake2x',
                        'Surname' => 'Faker',
                        'Forenames' => 'Prof',
                    ],
                    'blah3y' => [
                        'GUID' => 'blah3y',
                        'Surname' => 'McManus',
                        'Forenames' => 'Mark',
                    ],
                ],
                'Externals' => [
                    'fake3x' => [
                        'GUID' => 'fake3x',
                        'Surname' => 'Faker',
                        'Forenames' => 'Prof',
                    ],
                    'blah4y' => [
                        'GUID' => 'blah4y',
                        'Surname' => 'McManus',
                        'Forenames' => 'Mark',
                    ],
                ],
            ],
        ];
    }

    protected function getCourse2()
    {
        return [
            'Code' => 'TEST4321',
            'Title' => 'Fake Course 4321',
            'ActiveFlag' => 'Yes',
            'Discipline' => 'Discipline the second',
            'Exam' => [
                'Setters' => [
                    'doc2w' => [
                        'GUID' => 'doc2w',
                        'Surname' => 'Baker',
                        'Forenames' => 'Tom',
                    ],
                    'blah2y' => [
                        'GUID' => 'blah2y',
                        'Surname' => 'McManus',
                        'Forenames' => 'Mark',
                    ],
                ],
                'Moderators' => [
                    'blah2y' => [
                        'GUID' => 'blah2y',
                        'Surname' => 'McManus',
                        'Forenames' => 'Mark',
                    ],
                ],
                'Externals' => [
                    'fake2x' => [
                        'GUID' => 'fake2x',
                        'Surname' => 'Faker',
                        'Forenames' => 'Prof',
                    ],
                ],
            ],
        ];
    }
}
