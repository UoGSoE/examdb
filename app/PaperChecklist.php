<?php

namespace App;

use App\Scopes\CurrentScope;
use Illuminate\Database\Eloquent\Model;

class PaperChecklist extends Model
{
    const CURRENT_VERSION = 1;

    protected $guarded = [];

    protected $touches = ['course'];

    protected $casts = [
        'archived_at' => 'datetime',
        'fields' => 'array',
    ];

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new CurrentScope);
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function scopeArchived($query)
    {
        return $query->where('archived_at', '!=', null);
    }

    public static function makeDefault(Course $course, string $category): PaperChecklist
    {
        return new static([
            'id' => null,
            'version' => static::CURRENT_VERSION,
            'course_id' => $course->id,
            'category' => $category,
            'year' => $course->year,
            'scqf_level' => null,
            'course_credits' => null,
            'course_coordinator_setting_comments' => null,
            'moderator_agree_marks_appropriate' => true,
            'moderator_inappropriate_comments' => null,
            'moderator_marks_adjusted' => false,
            'moderator_reason_marks_adjusted' => null,
            'moderator_further_comments' => null,
            'moderator_approved_at' => null,
            'course_coordinator_moderating_comments' => null,
            'external_agree_with_moderator' => true,
            'external_rational' => null,
            'external_futher_comments' => null,
            'external_completed_at' => null,
            'archived_at' => null,
        ]);
    }

    public function isArchived(): bool
    {
        return $this->archived_at != null;
    }

    public function archive()
    {
        $this->update(['archived_at' => now()]);
    }

    public function getPreviousChecklist()
    {
        return $this->where('id', '<', $this->id)
                ->where('course_id', '=', $this->course_id)
                ->where('category', '=', $this->category)
                ->max('id');
    }

    public function getNextChecklist()
    {
        return $this->where('id', '>', $this->id)
            ->where('course_id', '=', $this->course_id)
            ->where('category', '=', $this->category)
            ->min('id');
    }
}
