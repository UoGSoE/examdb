<?php

namespace App;

use App\Events\PaperApproved;
use App\Events\PaperUnapproved;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class Course extends Model
{
    protected $guarded = [];

    protected $casts = [
        'setter_approved' => 'boolean',
        'moderator_approved' => 'boolean',
        'external_approved' => 'boolean',
    ];

    public function staff()
    {
        return $this->belongsToMany(User::class, 'course_user', 'course_id', 'user_id')->withPivot('is_moderator', 'is_setter', 'is_external');
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

    public function solutions()
    {
        return $this->hasMany(Paper::class);
    }

    public function mainSolutions()
    {
        return $this->solutions()->main();
    }

    public function resitSolutions()
    {
        return $this->solutions()->resit();
    }

    public function addPaper(string $category, string $subcategory, UploadedFile $file): Paper
    {
        if (!in_array($category, Paper::VALID_CATEGORIES)) {
            throw new \InvalidArgumentException('Invalid category');
        }

        $randomName = str_random(64);
        $filename = "papers/{$this->id}/{$category}/{$randomName}.dat";
        Storage::disk('exampapers')->put($filename, encrypt($file->get()));
        // $filename = $file->store("papers/{$this->id}/{$category}", 'exampapers');

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
        return static::where('code', '=', $code)->first();
    }

    /**
     * Create a course based on import data from the WLM
     *
     */
    public static function fromWlmData(array $wlmCourse): Course
    {
        $code = $wlmCourse['Code'];
        $title = $wlmCourse['Title'];
        $course = static::findByCode($code);
        if (!$course) {
            $course = new static(['code' => $code]);
        }
        $course->is_active = $course->getWlmStatus($wlmCourse);
        $course->title = $title;
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
}
