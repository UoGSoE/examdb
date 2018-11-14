<?php

namespace App\Wlm;

class FakeBrokenWlmClient implements WlmClientInterface
{
    public function getCourses()
    {
        throw new \Exception('Broken WLM test exception');
    }

    public function getCourse($code)
    {
    }

    public function getStaff($guid)
    {
    }
}
