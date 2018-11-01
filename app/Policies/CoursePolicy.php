<?php

namespace App\Policies;

use App\User;
use App\Course;
use Illuminate\Auth\Access\HandlesAuthorization;

class CoursePolicy
{
    use HandlesAuthorization;

    public function before($user)
    {
        if ($user->isAdmin()) {
            return true;
        }
    }

    public function show(User $user, Course $course)
    {
        return $user->isSetterFor($course) or
            $user->isModeratorFor($course) or
            $user->isExternalFor($course);
    }
}
