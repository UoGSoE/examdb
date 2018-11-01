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

    public function setting()
    {
        return $this->belongsToMany(Course::class, 'course_setters', 'user_id', 'course_id');
    }

    public function markAsSetter(Course $course)
    {
        $this->setting()->attach($course);
    }

    public function isSetterFor(Course $course)
    {
        return !!$this->setting()->where('course_id', '=', $course->id)->first();
    }

    public function isAdmin()
    {
        return $this->is_admin;
    }

    public function isExternal()
    {
        return strpos('@', $this->username) !== false;
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
