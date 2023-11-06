<?php

namespace App\Policies;

use App\Models\Course;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Support\Facades\Route;

class CoursePolicy
{
    use HandlesAuthorization;

    public function before($user, $ability)
    {
        if ($user->isAdmin()) {
            return true;
        }
        if (Route::currentRouteName() == 'api.course.checklist.show') {
            return true;
        }
    }

    public function show(User $user, Course $course)
    {
        return $user->isSetterFor($course) or
            $user->isModeratorFor($course) or
            $user->isExternalFor($course);
    }

    public function update(User $user, Course $course): bool
    {
        return $user->isSetterFor($course) or
            $user->isModeratorFor($course);
    }

    public function approve(User $user, Course $course)
    {
        return $user->isModeratorFor($course) or $user->isExternalFor($course);
    }
}
