<?php

namespace App;

use App\Scopes\CurrentScope;
use Illuminate\Database\Eloquent\Model;

/*
 <select name="previous_id" id="previous_id" wire:model="previousId">
                            <input class="input" type="text" wire:model="checklist.fields.">
                        <input class="input" type="text" wire:model.lazy="checklist.fields.">
                            <input class="input" type="text" wire:model.lazy="checklist.fields.">
                        <input class="input" type="text" wire:model.lazy="checklist.fields.">
                        <input class="input" type="text" wire:model.lazy="checklist.fields.">
            <select wire:model="checklist.fields.">
        <input class="input" type="text" wire:model="checklist.fields.">
                <input class="input" type="text" wire:model="checklist.fields.">
                <input class="input" type="text" wire:model="checklist.fields.">
                <input class="input" type="text" wire:model="checklist.fields." value="{{ $course->moderators->pluck('full_name')->implode(', ') }}">
                <input class="input" x-ref="passed_to_moderator" type="text" wire:model.lazy="checklist.fields.">
                <select wire:model="checklist.field.overall_quality_appropriate">
            <textarea class="textarea" wire:model="checklist.fields." id=""></textarea>
                <select wire:model="checklist.fields.">
            <textarea class="textarea" wire:model="checklist.fields." id=""></textarea>
            <textarea class="textarea" wire:model="checklist.fields." id=""></textarea>
                    <input class="input" x-ref="moderator_completed_at" type="text" wire:model.lazy="checklist.fields.">
            <textarea class="textarea" wire:model="checklist.fields." id=""></textarea>
                <select wire:model="checklist.fields.">
            <textarea class="textarea" wire:model="checklist.fields." id=""></textarea>
                <select wire:model="checklist.fields.">
            <textarea class="textarea" wire:model="checklist.fields." id=""></textarea>
            <textarea class="textarea" wire:model="checklist.fields." id=""></textarea>
                    <input class="input" x-ref="moderator_solutions_at" type="text" wire:model.lazy="checklist.fields.">
            <textarea class="textarea" wire:model="checklist.fields." id=""></textarea>
            <input type="text" class="input" wire:model="checklist.fields.">
                <select wire:model="checklist.fields.">
            <textarea class="textarea" wire:model="checklist.fields." id=""></textarea>
            <textarea class="textarea" wire:model="checklist.fields." id=""></textarea>
                    <input class="input" x-ref="external_signed_at" type="text" wire:model="checklist.fields.">
                    */
class PaperChecklist extends Model
{
    const CURRENT_VERSION = 1;

    protected $guarded = [];

    protected $touches = ['course'];

    protected $casts = [
        'archived_at' => 'datetime',
        'fields' => 'array',
    ];

    const SETTER_FIELDS = [
        'course_code',
        'course_title',
        'year',
        'scqf_level',
        'course_credits',
        'setter_reviews',
        'assessment_title',
        'assignment_weighting',
        'number_markers',
        'moderators',
        'passed_to_moderator',
        'setter_comments_to_moderator',
        'solution_setter_comments',
    ];
    const MODERATOR_FIELDS = [
        'why_innapropriate',
        'should_revise_questions',
        'recommended_revisions',
        'moderator_comments',
        'moderator_completed_at',
        'solution_marks_appropriate',
        'moderator_solution_innapropriate_comments',
        'solutions_marks_adjusted',
        'solution_adjustment_comments',
        'solution_moderator_comments',
        'moderator_solutions_at',
    ];
    const EXTERNAL_FIELDS = [
        'external_examiner_name',
        'external_agrees_with_moderator',
        'external_reason',
        'external_comments',
        'external_signed_at',
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

    public function shouldNotifyModerator(): bool
    {
        if (! array_key_exists('passed_to_moderator', $this->fields)) {
            return false;
        }

        return (bool) $this->fields['passed_to_moderator'];
    }
}
