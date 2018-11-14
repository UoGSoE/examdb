<?php

namespace App\Wlm;

interface WlmClientInterface
{
    public function getCourses();

    public function getCourse($code);

    public function getStaff($guid);
}
