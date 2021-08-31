<?php

namespace App;

use App\AcademicSession;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Cache;
use App\CanBeCreatedFromOutsideSources;
use Spatie\Activitylog\Models\Activity;
use Illuminate\Notifications\Notifiable;
use Lab404\Impersonate\Models\Impersonate;
use App\Scopes\CurrentAcademicSessionScope;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

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

    protected static function booted()
    {
        static::addGlobalScope(new CurrentAcademicSessionScope);
    }

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

    public function scopeForAcademicSession($query, AcademicSession $session)
    {
        return $query->where('academic_session_id', '=', $session->id);
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

    public function getCurrentAcademicSession()
    {
        if (session()->missing('academic_session')) {
            return AcademicSession::getDefault();
        }
        return AcademicSession::findBySession(session('academic_session'));
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
        $cacheKey = $this->id . '_is_setter_for_' . $course->code;
        return Cache::remember(
            $cacheKey,
            5,
            fn () =>  $this->courses()->where('course_user.course_id', '=', $course->id)
            ->wherePivot('is_setter', true)
            ->count() > 0
        );
        return $this->$cacheKey;
    }

    public function isModeratorFor(Course $course): bool
    {
        $cacheKey = $this->id . '_is_moderator_for_' . $course->code;
        return Cache::remember(
            $cacheKey,
            5,
            fn () => $this->courses()->where('course_user.course_id', '=', $course->id)
                        ->wherePivot('is_moderator', true)
                        ->count() > 0
        );
    }

    public function isExternalFor(Course $course): bool
    {
        $cacheKey = $this->id . '_is_external_for_' . $course->code;
        return Cache::remember(
            $cacheKey,
            5,
            fn () =>  $this->courses()->where('course_user.course_id', '=', $course->id)
            ->wherePivot('is_external', true)
            ->count() > 0
        );
    }

    public function toggleAdmin()
    {
        $this->is_admin = ! $this->is_admin;
        $this->save();

        // this reflects the current admin status of the user for all academic sessions
        // so that people can be admins (or not) for previous/future sessions
        self::withoutGlobalScope(CurrentAcademicSessionScope::class)
            ->where('username', '=', $this->username)
            ->update(['is_admin' => $this->is_admin]);

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
