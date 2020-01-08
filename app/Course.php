<?php

namespace App;

use Illuminate\Support\Str;
use App\Events\PaperApproved;
use App\Events\PaperUnapproved;
use Illuminate\Http\UploadedFile;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\SoftDeletes;

class Course extends Model
{
    use SoftDeletes;

    protected $guarded = [];

    protected $casts = [
        'setter_approved_main' => 'boolean',
        'setter_approved_resit' => 'boolean',
        'moderator_approved_main' => 'boolean',
        'moderator_approved_resit' => 'boolean',
        'external_approved' => 'boolean',
        'external_notified' => 'boolean',
    ];

    public function staff()
    {
        return $this->belongsToMany(User::class, 'course_user', 'course_id', 'user_id')
                    ->withPivot('is_moderator', 'is_setter', 'is_external');
    }

    public function moderators()
    {
        return $this->belongsToMany(User::class, 'course_user')->wherePivot('is_moderator', true);
    }

    public function setters()
    {
        return $this->belongsToMany(User::class, 'course_user')->wherePivot('is_setter', true);
    }

    public function externals()
    {
        return $this->belongsToMany(User::class, 'course_user')->wherePivot('is_external', true);
    }

    public function discipline()
    {
        return $this->belongsTo(Discipline::class);
    }

    public function papers()
    {
        return $this->hasMany(Paper::class);
    }

    public function mainPapers()
    {
        return $this->papers()->main();
    }

    public function resitPapers()
    {
        return $this->papers()->resit();
    }

    public function resit2Papers()
    {
        return $this->papers()->resit2();
    }

    public function scopeExternalsNotNotified($query)
    {
        return $query->where('external_notified', '=', false);
    }

    public function scopeForArea($query, $area)
    {
        $codePrefix = $area == 'uestc' ? 'UESTC' : 'ENG';
        return $query->where('code', 'like', $codePrefix . '%');
    }

    public function markExternalNotified()
    {
        $this->update(['external_notified' => true]);
    }

    public function markExternalNotNotified()
    {
        $this->update(['external_notified' => false]);
    }

    public function externalNotified()
    {
        return $this->external_notified;
    }

    public function getLatestUploadAttribute()
    {
        return $this->papers()->latest()->first();
    }

    public function addPaper(string $category, string $subcategory, UploadedFile $file): Paper
    {
        if (!in_array($category, Paper::VALID_CATEGORIES)) {
            throw new \InvalidArgumentException('Invalid category');
        }

        $randomName = Str::random(64) . '_' . now()->format('d-m-Y');
        $filename = "papers/{$this->id}/{$category}/{$randomName}.dat";
        Storage::disk('exampapers')->put($filename, encrypt($file->get()));

        return $this->papers()->create([
            'category' => $category,
            'subcategory' => $subcategory,
            'user_id' => auth()->id(),
            'filename' => $filename,
            'original_filename' => $file->getClientOriginalName(),
            'mimetype' => $file->getClientMimeType(),
            'size' => $file->getSize(),
        ]);
    }

    public function paperApprovedBy(User $user, string $category)
    {
        if ($user->isSetterFor($this)) {
            $this->update(["setter_approved_{$category}" => true]);
            event(new PaperApproved($this, $user, $category));
            return;
        }
        if ($user->isModeratorFor($this)) {
            $this->update(["moderator_approved_{$category}" => true]);
            event(new PaperApproved($this, $user, $category));
            return;
        }
        if ($user->isExternalFor($this)) {
            $this->update(["external_approved_{$category}" => true]);
            event(new PaperApproved($this, $user, $category));
            return;
        }

        throw new \DomainException('User is not associated with this course');
    }

    public function paperUnapprovedBy(User $user, string $category)
    {
        if ($user->isSetterFor($this)) {
            $this->update(["setter_approved_{$category}" => false]);
            event(new PaperUnapproved($this, $user, $category));
            return;
        }
        if ($user->isModeratorFor($this)) {
            $this->update(["moderator_approved_{$category}" => false]);
            event(new PaperUnapproved($this, $user, $category));
            return;
        }
        if ($user->isExternalFor($this)) {
            $this->update(["external_approved_{$category}" => false]);
            event(new PaperUnapproved($this, $user, $category));
            return;
        }

        throw new \DomainException('User is not associated with this course');
    }

    public function isApprovedBySetter(string $category): bool
    {
        $key = "setter_approved_{$category}";
        return $this->$key;
    }

    public function isApprovedByModerator(string $category): bool
    {
        $key = "moderator_approved_{$category}";
        return $this->$key;
    }

    public function isApprovedBy(User $user, string $category): bool
    {
        if ($user->isSetterFor($this)) {
            $key = "setter_approved_{$category}";
            return $this->$key;
        }
        if ($user->isModeratorFor($this)) {
            $key = "moderator_approved_{$category}";
            return $this->$key;
        }
        if ($user->isExternalFor($this)) {
            $key = "external_approved_{$category}";
            return $this->$key;
        }

        if ($user->isAdmin()) {
            return false;
        }

        throw new \DomainException('User is not associated with this course');
    }

    public function isFullyApproved(): bool
    {
        return $this->setter_approved_main and $this->setter_approved_resit and
            $this->moderator_approved_main and $this->moderator_approved_resit;
    }

    public function isntFullyApproved(): bool
    {
        return ! $this->isFullyApproved();
    }

    public function getUserApprovedMainAttribute(? User $user): bool
    {
        if (!$user) {
            $user = auth()->user();
        }
        return $this->isApprovedBy($user, 'main');
    }

    public function getUserApprovedResitAttribute(? User $user): bool
    {
        if (!$user) {
            $user = auth()->user();
        }
        return $this->isApprovedBy($user, 'resit');
    }

    public function getFullNameAttribute()
    {
        return $this->code . ' ' . $this->title;
    }

    public function datePaperAdded(string $category, string $subcategory): string
    {
        $paper = $this->papers
            ->where('category', $category)
            ->where('subcategory', $subcategory)
            ->sortBy('created_at')
            ->last();
        if (!$paper) {
            return '';
        }
        return $paper->created_at->format('d/m/Y');
    }

    public static function findByCode($code)
    {
        return static::withTrashed()->where('code', '=', $code)->first();
    }

    /**
     * Create a course based on import data from the WLM
     *
     */
    public static function fromWlmData(array $wlmCourse): Course
    {
        $code = $wlmCourse['Code'];
        $title = $wlmCourse['Title'];
        $disciplineTitle = trim($wlmCourse['Discipline']);
        $discipline = Discipline::firstOrCreate(['title' => $disciplineTitle]);
        $course = static::findByCode($code);
        if (!$course) {
            $course = new static(['code' => $code]);
        }
        $course->is_active = $course->getWlmStatus($wlmCourse);
        $course->title = $title;
        $course->discipline()->associate($discipline);
        $course->save();
        return $course;
    }

    protected function getWlmStatus($wlmCourse)
    {
        if (!array_key_exists('CurrentFlag', $wlmCourse)) {
            return false;
        }
        if ($wlmCourse['CurrentFlag'] === 'Yes') {
            return true;
        }
        return false;
    }

    public function isUestc()
    {
        return preg_match('/^UESTC/i', $this->code) === 1;
    }

    public function getIsUestcAttribute()
    {
        return $this->isUestc();
    }

    public function isDisabled()
    {
        return $this->deleted_at != null;
    }

    public function disable()
    {
        $this->delete();
    }

    public function enable()
    {
        $this->restore();
    }
}
