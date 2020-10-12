<?php

namespace App;

use App\CanBeCreatedFromOutsideSources;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\URL;
use Lab404\Impersonate\Models\Impersonate;
use Spatie\Activitylog\Models\Activity;

class User extends Authenticatable
{
    use HasFactory;
    use Notifiable;
    use CanBeCreatedFromOutsideSources;
    use Impersonate;
    use SoftDeletes;

    protected $guarded = [];

    protected $hidden = [
        'password', 'remember_token',
    ];

    protected $casts = [
        'is_admin' => 'boolean',
        'is_staff' => 'boolean',
        'is_external' => 'boolean',
    ];

    protected $appends = ['full_name'];

    public function courses()
    {
        return $this->belongsToMany(Course::class)->withPivot('is_setter', 'is_moderator', 'is_external');
    }

    public function logs()
    {
        return $this->hasMany(Activity::class, 'causer_id')->orderByDesc('created_at');
    }

    public function papers()
    {
        return $this->hasMany(Paper::class);
    }

    public static function getStaffForVueSelect()
    {
        return static::orderBy('surname')
            ->get()
            ->map(function ($user) {
                return [
                    'label' => $user->full_name." ({$user->username})",
                    'value' => $user->id,
                ];
            });
    }

    public static function getExternalsForVueSelect()
    {
        return static::where('is_external', '=', true)
            ->orderBy('surname')
            ->get()
            ->map(function ($user) {
                return [
                    'label' => $user->full_name." ({$user->username})",
                    'value' => $user->id,
                ];
            });
    }

    public function getCourses(string $userTypeField = null)
    {
        $query = $this->courses();
        if ($userTypeField) {
            $query = $query->wherePivot($userTypeField, true);
        }

        return $query->latest('updated_at')->get();
    }

    public function getAllUploads()
    {
        return $this->papers()
            ->where('subcategory', '!=', Paper::COMMENT_SUBCATEGORY)
            ->with('course')
            ->orderByDesc('created_at')
            ->get();
    }

    public function markAsSetter(Course $course)
    {
        $this->courses()->syncWithoutDetaching([$course->id]);
        $this->courses()->updateExistingPivot(
            $course->id,
            ['is_setter' => true, 'is_external' => false]
        );
    }

    public function markAsModerator(Course $course)
    {
        $this->courses()->syncWithoutDetaching([$course->id]);
        $this->courses()->updateExistingPivot(
            $course->id,
            ['is_moderator' => true, 'is_external' => false]
        );
    }

    public function markAsExternal(Course $course)
    {
        $this->courses()->syncWithoutDetaching([$course->id]);
        $this->courses()->updateExistingPivot(
            $course->id,
            ['is_setter' => false, 'is_moderator' => false, 'is_external' => true]
        );
    }

    public function isSetterFor(Course $course): bool
    {
        return $this->courses()->where('course_user.course_id', '=', $course->id)
            ->wherePivot('is_setter', true)
            ->count() > 0;
    }

    public function isModeratorFor(Course $course): bool
    {
        return $this->courses()->where('course_user.course_id', '=', $course->id)
            ->wherePivot('is_moderator', true)
            ->count() > 0;
    }

    public function isExternalFor(Course $course): bool
    {
        return $this->courses()->where('course_user.course_id', '=', $course->id)
            ->wherePivot('is_external', true)
            ->count() > 0;
    }

    public function toggleAdmin()
    {
        $this->is_admin = ! $this->is_admin;
        $this->save();
        activity()
            ->causedBy(request()->user())
            ->log(
                "Toggled admin status for {$this->full_name}"
            );
    }

    public function makeAdmin()
    {
        $this->update(['is_admin' => true]);
    }

    public function isAdmin()
    {
        return (bool) $this->is_admin;
    }

    public function isExternal()
    {
        return strpos($this->username, '@') !== false;
    }

    public function isInternal()
    {
        return ! $this->isExternal();
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
        return $this->forenames.' '.$this->surname;
    }

    public static function findByUsername($username)
    {
        return static::withTrashed()->where('username', '=', $username)->first();
    }

    public function canImpersonate()
    {
        return $this->isAdmin();
    }

    public function anonymise()
    {
        $this->update([
            'username' => 'gdpr'.$this->id,
            'email' => 'gdpr'.$this->id.'@glasgow.ac.uk',
            'surname' => 'anon',
            'forenames' => 'anon',
        ]);
    }
}
