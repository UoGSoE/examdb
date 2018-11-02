<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\URL;

class User extends Authenticatable
{
    use Notifiable;

    protected $guarded = [];

    protected $hidden = [
        'password', 'remember_token',
    ];

    protected $casts = [
        'is_admin' => 'boolean',
    ];

    protected $appends = ['full_name'];

    public function courses()
    {
        return $this->belongsToMany(Course::class)->withPivot('is_setter', 'is_moderator', 'is_external');
    }

    public function markAsSetter(Course $course)
    {
        $this->courses()->syncWithoutDetaching([$course->id]);
        $this->courses()->updateExistingPivot($course->id, ['is_setter' => true]);
    }

    public function markAsExternal(Course $course)
    {
        $this->courses()->syncWithoutDetaching([$course->id]);
        $this->courses()->updateExistingPivot($course->id, ['is_external' => true]);
    }

    public function isSetterFor(Course $course) : bool
    {
        return $this->courses()->where('course_user.course_id', '=', $course->id)->wherePivot('is_setter', true)->count() > 0;
    }

    public function isModeratorFor(Course $course) : bool
    {
        return $this->courses()->where('course_user.course_id', '=', $course->id)->wherePivot('is_moderator', true)->count() > 0;
    }

    public function isExternalFor(Course $course) : bool
    {
        return $this->courses()->where('course_user.course_id', '=', $course->id)->wherePivot('is_external', true)->count() > 0;
    }

    public function isAdmin()
    {
        return $this->is_admin;
    }

    public function isExternal()
    {
        return strpos($this->username, '@') !== false;
    }

    public function isInternal()
    {
        return !$this->isExternal();
    }

    public function generateLoginUrl()
    {
        return URL::temporarySignedRoute(
            'external-login',
            now()->addMinutes(config('exampapers.login_link_minutes', 60)),
            ['user' => $this->id]
        );
    }

    public function getFullNameAttribute()
    {
        return $this->forenames . ' ' . $this->surname;
    }

}
