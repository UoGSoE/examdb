<?php

namespace App;

use App\Events\PaperApproved;
use App\Events\PaperUnapproved;
use App\Mail\ExternalHasUpdatedTheChecklist;
use App\Mail\ModeratorHasUpdatedTheChecklist;
use App\Mail\SetterHasUpdatedTheChecklist;
use App\Scopes\CurrentAcademicSessionScope;
use App\Scopes\CurrentScope;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use InvalidArgumentException;

class Course extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $guarded = [];

    protected $casts = [
        'is_examined' => 'boolean',
        'moderator_approved_main' => 'boolean',
        'moderator_approved_resit' => 'boolean',
        'moderator_approved_assessment' => 'boolean',
        'external_approved_main' => 'boolean',
        'external_approved_resit' => 'boolean',
        'external_approved_assessment' => 'boolean',
        'external_notified' => 'boolean',
        'registry_approved_main' => 'boolean',
        'registry_approved_resit' => 'boolean',
    ];

    public $flagsToClearOnDuplication = [
        'moderator_approved_main',
        'moderator_approved_resit',
        'external_approved_main',
        'external_approved_resit',
        'moderator_approved_assessment',
        'external_approved_assessment',
        'external_notified',
        'registry_approved_main',
        'registry_approved_resit',
    ];

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new CurrentAcademicSessionScope);
    }

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

    public function checklists()
    {
        return $this->hasMany(PaperChecklist::class);
    }

    public function papers()
    {
        return $this->hasMany(Paper::class);
    }

    public function archivedPapers()
    {
        return $this->hasMany(Paper::class)->withoutGlobalScope(CurrentScope::class)->archived();
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

    public function scopeForSemester($query, int $semester)
    {
        return $query->where('semester', '=', $semester);
    }

    public function scopeExternalsNotNotified($query)
    {
        return $query->where('external_notified', '=', false);
    }

    public function scopeForArea($query, $area)
    {
        if ($area == 'uestc') {
            return $query->where('code', 'like', 'UESTC%');
        }
        return $query->where('code', 'not like', 'UESTC%');
    }

    public function scopeForDiscipline($query, $discipline)
    {
        return $query->where('discipline_id', '=', $discipline->id);
    }

    public function scopeMissingPaperForRegistry($query)
    {
        return $query->papers()->where('subcategory', '=', Paper::PAPER_FOR_REGISTRY);
    }

    public function scopeExamined($query)
    {
        return $query->where('is_examined', '=', true);
    }

    /**
     * This is horrific
     * TODO : make it not horrific.
     */
    public function addChecklist(array $fields, string $category): PaperChecklist
    {
        if (! in_array($category, ['main', 'resit', 'assessment'])) {
            abort(422, 'Invalid category '.$category);
        }

        // if there was an existing checklist we get it's fields so we can merge them in with the new values
        if ($this->hasPreviousChecklists($category)) {
            $previousFields = $this->checklists()->where('category', '=', $category)->latest('id')->first()['fields'];
        } else {
            $previousFields = $this->getDefaultChecklistFields();
        }

        // figure out which fields on the form the user is allowed to update
        $fieldsToUpdate = [];
        if (auth()->check() && auth()->user()->isSetterFor($this)) {
            $fieldsToUpdate = array_merge($fieldsToUpdate, PaperChecklist::SETTER_FIELDS);
        }
        if (auth()->check() && auth()->user()->isModeratorFor($this)) {
            $fieldsToUpdate = array_merge($fieldsToUpdate, PaperChecklist::MODERATOR_FIELDS);
        }
        if (auth()->check() && auth()->user()->isExternalFor($this)) {
            $fieldsToUpdate = array_merge($fieldsToUpdate, PaperChecklist::EXTERNAL_FIELDS);
        }

        $checklist = $this->checklists()->create([
            'category' => $category,
            'user_id' => optional(auth()->user())->id,
            // we merge only the fields the user is allowed to update with any existing fields from a previous checklost
            'fields' => array_merge($previousFields, Arr::only($fields, $fieldsToUpdate)),
        ]);

        // figure out if the moderator has fully approved the paper
        if (auth()->check() && auth()->user()->isModeratorFor($this)) {
            $fieldName = "moderator_approved_{$category}";
            $this->$fieldName = (bool) (
                Arr::get($fields, 'overall_quality_appropriate', false)
                &&
                ! Arr::get($fields, 'should_revise_questions', false)
                &&
                Arr::get($fields, 'solution_marks_appropriate', false)
                &&
                ! Arr::get($fields, 'solutions_marks_adjusted', false)
            );
        }

        if (auth()->check() && auth()->user()->isExternalFor($this)) {
            // figure out if the external has approved the paper
            $fieldName = "external_approved_{$category}";
            $this->$fieldName = (bool) Arr::get($fields, 'external_agrees_with_moderator', false);
        }

        $this->save();

        activity()
            ->causedBy(request()->user())
            ->log(
                "Added a {$category} checklist for {$this->code}"
            );

        $flashMessage = 'Checklist Saved';

        if (auth()->check() && auth()->user()->isSetterFor($this) && $checklist->shouldNotifyModerator()) {
            $area = str_contains($this, 'ENG') ? 'glasgow' : 'uestc';
            $optionName = "{$area}_internal_moderation_deadline";
            $deadline = '';
            if (option($optionName)) {
                $deadline = Carbon::createFromFormat('Y-m-d', option($optionName))->format('d/m/Y');
            }

            $this->moderators->pluck('email')->each(function ($email) use ($deadline) {
                Mail::to($email)->queue(new SetterHasUpdatedTheChecklist($this, $deadline));
            });

            $flashMessage = 'Checklist Saved - moderators notified';
        }

        if (auth()->check() && auth()->user()->isModeratorFor($this)) {
            $this->setters->pluck('email')->each(function ($email) {
                Mail::to($email)->queue(new ModeratorHasUpdatedTheChecklist($this));
            });
            $flashMessage = 'Checklist Saved - setters notified';
        }

        if (auth()->check() && auth()->user()->isExternalFor($this)) {
            $this->setters->pluck('email')->each(function ($email) {
                Mail::to($email)->queue(new ExternalHasUpdatedTheChecklist($this));
            });
        }

        session()->flash('success', $flashMessage);

        return $checklist;
    }

    public function getNewChecklist(string $category): PaperChecklist
    {
        $checklist = $this->checklists()->where('category', '=', $category)->latest()->first();
        if (! $checklist) {
            $checklist = new PaperChecklist([
                'course_id' => $this->id,
                'category' => $category,
                'version' => PaperChecklist::CURRENT_VERSION,
                'fields' => $this->getDefaultChecklistFields(),
            ]);
        }

        return $checklist->replicate();
    }

    public function getDefaultChecklistFields()
    {
        return [
            'course_code' => $this->code,
            'course_title' => $this->title,
            'year' => $this->year,
            'moderators' => $this->moderators->pluck('full_name')->implode(', '),
            'scqf_level' => '',
            'course_credits' => '',
            'setter_reviews' => '',
            'assessment_title' => '',
            'assignment_weighting' => '',
            'number_markers' => '',
            'passed_to_moderator' => '',
            'setter_comments_to_moderator' => '',
            'solution_setter_comments' => '',
            'overall_quality_appropriate' => "1",
            'why_innapropriate' => '',
            'should_revise_questions' => "1",
            'recommended_revisions' => '',
            'moderator_comments' => '',
            'moderator_completed_at' => '',
            'solution_marks_appropriate' => "1",
            'moderator_solution_innapropriate_comments' => '',
            'solutions_marks_adjusted' => "1",
            'solution_adjustment_comments' => '',
            'solution_moderator_comments' => '',
            'moderator_solutions_at' => '',
            'external_examiner_name' => '',
            'external_agrees_with_moderator' => "0",
            'external_reason' => '',
            'external_comments' => '',
            'external_signed_at' => '',
        ];
    }
    public function hasSetterChecklist(string $category)
    {
        return $this->checklists
            ->where('category', $category)
            ->whereIn('user_id', $this->setters->pluck('id'))
            ->count() > 0;
    }

    public function hasModeratorChecklist(string $category)
    {
        return $this->checklists
            ->where('category', $category)
            ->whereIn('user_id', $this->moderators->pluck('id'))
            ->count() > 0;
    }

    public function hasExternalChecklist(string $category)
    {
        return $this->checklists
            ->where('category', $category)
            ->whereIn('user_id', $this->externals->pluck('id'))
            ->count() > 0;
    }

    public function hasPreviousChecklists(string $category): bool
    {
        return $this->checklists()->where('category', '=', $category)->count() > 0;
    }

    public function hasMoreChecklists(PaperChecklist $checklist, string $category): bool
    {
        if ($this->checklists()->where('category', '=', $category)->count() == 0) {
            return false;
        }

        return ! $this->checklists()->where('category', '=', $category)->latest()->first()->is($checklist);
    }

    public function getMainPapers()
    {
        $papers = $this->mainPapers()->with(['user', 'comments'])->latest()->get();
        $checklists = $this->checklists()->where('category', '=', 'main')->latest()->get();
        return $this->combinePapersAndChecklists($papers, $checklists);
    }

    public function getResitPapers()
    {
        $papers = $this->resitPapers()->with(['user', 'comments'])->latest()->get();
        $checklists = $this->checklists()->where('category', '=', 'resit')->latest()->get();
        return $this->combinePapersAndChecklists($papers, $checklists);
    }

    public function getResit2Papers()
    {
        $papers = $this->resit2Papers()->with(['user', 'comments'])->latest()->get();
        $checklists = $this->checklists()->where('category', '=', 'resit2')->latest()->get();
        return $this->combinePapersAndChecklists($papers, $checklists);
    }

    protected function combinePapersAndChecklists($papers, $checklists)
    {
        $checklistsAsPapers = $checklists->map(function ($checklist) {
            $fake = new FakePaper([
                'id' => Str::random(64),
                'category' => 'main',
                'subcategory' => 'Updated Checklist',
                'user_id' => $checklist->user_id,
                'course_id' => $this->id,
                'created_at' => $checklist->created_at,
                'formatted_date' => $checklist->created_at->format('d/m/Y H:i'),
                'diff_for_humans' => $checklist->created_at->diffForHumans(),
            ]);
            $fake->load('user');
            return $fake;
        });
        foreach ($checklistsAsPapers as $fakePaper) {
            $papers->push($fakePaper);
        }

        return $papers->sortByDesc('created_at')->values();
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

    public function addPaper(string $category, string $subcategory, UploadedFile $file): Paper
    {
        if (! in_array($category, Paper::VALID_CATEGORIES)) {
            throw new \InvalidArgumentException('Invalid category : '.$category);
        }

        $randomName = Str::random(64).'_'.now()->format('d-m-Y');
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
        if ($user->isModeratorFor($this)) {
            $this->update(["moderator_approved_{$category}" => true]);
        }
        if ($user->isExternalFor($this)) {
            $this->update(["external_approved_{$category}" => true]);
        }
        event(new PaperApproved($this, $user, $category));
    }

    public function paperUnapprovedBy(User $user, string $category)
    {
        if ($user->isModeratorFor($this)) {
            $this->update(["moderator_approved_{$category}" => false]);
        }
        if ($user->isExternalFor($this)) {
            $this->update(["external_approved_{$category}" => false]);
        }
        event(new PaperUnapproved($this, $user, $category));
    }

    public function isApprovedByModerator(string $category): bool
    {
        $key = "moderator_approved_{$category}";

        return (bool) $this->$key;
    }

    public function isApprovedByExternal(string $category): bool
    {
        $key = "external_approved_{$category}";

        return (bool) $this->$key;
    }

    public function isApprovedBy(User $user, string $category): bool
    {
        if ($user->isModeratorFor($this)) {
            $key = "moderator_approved_{$category}";

            return (bool) $this->$key;
        }

        if ($user->isExternalFor($this)) {
            $key = "external_approved_{$category}";

            return (bool) $this->$key;
        }

        return false;
    }

    public function isFullyApproved(): bool
    {
        return $this->moderator_approved_main and
               $this->moderator_approved_resit and
               $this->external_approved_main and
               $this->external_approved_resit;
    }

    public function isntFullyApproved(): bool
    {
        return ! $this->isFullyApproved();
    }

    public function isFullyApprovedByModerator()
    {
        return $this->isApprovedByModerator('main') && $this->isApprovedByModerator('resit');
    }

    public function isntFullyApprovedByModerator()
    {
        return ! $this->isFullyApprovedByModerator();
    }

    public function removeAllApprovals()
    {
        collect([
            'moderator_approved_main',
            'moderator_approved_resit',
            'external_approved_main',
            'external_approved_resit',
        ])->each(function ($field) {
            $this->update([$field => false]);
        });
    }

    public function getUserApprovedMainAttribute(? User $user): bool
    {
        if (! $user) {
            $user = auth()->user();
        }

        return $this->isApprovedBy($user, 'main');
    }

    public function getUserApprovedResitAttribute(? User $user): bool
    {
        if (! $user) {
            $user = auth()->user();
        }

        return $this->isApprovedBy($user, 'resit');
    }

    public function getFullNameAttribute()
    {
        return $this->code.' '.$this->title;
    }

    public function datePaperAdded(string $category, string $subcategory): string
    {
        $paper = $this->papers
            ->where('category', $category)
            ->filter(fn ($paper) => Str::startsWith($paper->subcategory, $subcategory))
            ->sortBy('created_at')
            ->last();
        if (! $paper) {
            return '';
        }

        return $paper->created_at->format('d/m/Y');
    }

    public static function findByCode($code)
    {
        return static::withTrashed()->where('code', '=', $code)->first();
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

    public function getYearAttribute()
    {
        $matches = [];
        preg_match('/[a-zA-Z]+(\d)(\d)+/', $this->code, $matches);
        if (empty($matches)) {
            return '';
        }

        return $matches[1];
    }

    public function getHasMainPaperForRegistryAttribute()
    {
        return $this->mainPapers->contains(fn ($paper) => Str::startsWith($paper->subcategory, Paper::PAPER_FOR_REGISTRY));
    }

    public function getHasResitPaperForRegistryAttribute()
    {
        return $this->resitPapers->contains(fn ($paper) => Str::startsWith($paper->subcategory, Paper::PAPER_FOR_REGISTRY));
    }

    public function approvePaperForRegistry(string $category = 'main')
    {
        $field = 'registry_approved_' . $category;

        $this->update([
            $field => true,
        ]);
    }

    public function paperForRegistryIsApproved(string $category = 'main')
    {
        $field = 'registry_approved_' . $category;

        return $this->$field;
    }

    /**
     * @throws InvalidArgumentException
     */
    public function createDuplicate(string $newCode): Course
    {
        if (preg_match('/^[A-Z]+[0-9]+/', $newCode) !== 1) {
            throw new InvalidArgumentException("New course code of {$newCode} looks invalid...");
        }


        $newCourse = $this->replicate();
        $newCourse->code = $newCode;
        foreach ($this->flagsToClearOnDuplication as $flag) {
            $newCourse->$flag = false;
        }
        $newCourse->save();
        $this->setters->each(fn ($setter) => $setter->markAsSetter($newCourse));
        $this->moderators->each(fn ($setter) => $setter->markAsModerator($newCourse));
        $this->externals->each(fn ($setter) => $setter->markAsExternal($newCourse));

        return $newCourse;
    }

    public function isExamined(): bool
    {
        return $this->is_examined;
    }

    public function isntExamined(): bool
    {
        return ! $this->isExamined();
    }
}
