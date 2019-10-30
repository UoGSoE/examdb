<?php

namespace App;

use App\Scopes\CurrentScope;
use Illuminate\Database\Eloquent\Model;

class Paper extends Model
{
    const PAPER_FOR_REGISTRY = 'Paper For Registry';
    const PAPER_CHECKLIST = 'Paper Checklist';
    const EXTERNAL_COMMENTS = 'External Examiner Comments';
    const SECOND_RESIT_CATEGORY = 'resit2';
    const VALID_CATEGORIES = ['main', 'resit', 'resit2'];

    protected $guarded = [];

    protected $casts = [
        'approved_setter' => 'boolean',
        'archived_at' => 'datetime',
    ];

    protected $appends = ['icon', 'formatted_date', 'diff_for_humans', 'formatted_size'];

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new CurrentScope);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function comments()
    {
        return $this->morphMany(Comment::class, 'commentable');
    }

    public function scopeReadyForExternals($query)
    {
        return $query->where('subcategory', '=', Paper::PAPER_CHECKLIST);
    }

    public function scopeMain($query)
    {
        return $query->where('category', '=', 'main');
    }

    public function scopeResit($query)
    {
        return $query->where('category', '=', 'resit');
    }

    public function scopeResit2($query)
    {
        return $query->where('category', '=', 'resit2');
    }

    public function scopeCurrent($query)
    {
        return $query->where('archived_at', '=', null);
    }

    public function scopeArchived($query)
    {
        return $query->where('archived_at', '!=', null);
    }

    public function isArchived(): bool
    {
        return $this->archived_at != null;
    }

    public function archive()
    {
        $this->update(['archived_at' => now()]);
    }

    public function addComment($comment)
    {
        $this->comments()->create([
            'user_id' => auth()->id(),
            'comment' => $comment,
        ]);
    }

    public function approvedBy(User $user)
    {
        $this->course->paperApprovedBy($user, $this->category);
    }

    public function getIconAttribute()
    {
        if ($this->isAPdf()) {
            return "far fa-file-pdf";
        }

        if ($this->isAWordDocument()) {
            return "far fa-file-word";
        }

        if ($this->isAZip()) {
            return "far fa-file-archive";
        }

        return "far fa-file";
    }

    public function getFormattedSizeAttribute()
    {
        // stolen from https://stackoverflow.com/a/2510459 as is tradition
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($this->size, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);
        return round($bytes) . ' ' . $units[$pow];
    }

    protected function isAPdf()
    {
        if ($this->mimetype === 'application/pdf') {
            return true;
        }
        if (preg_match('/.pdf$/i', $this->original_filename) === 1) {
            return true;
        }
        return false;
    }

    protected function isAWordDocument()
    {
        if ($this->mimetype === 'application/vnd.openxmlformats-officedocument.wordprocessingml.document') {
            return true;
        }
        if (preg_match('/.doc(?x)$/i', $this->original_filename) === 1) {
            return true;
        }
        return false;
    }

    protected function isAZip()
    {
        if ($this->mimetype === 'application/zip') {
            return true;
        }
        if (preg_match('/.zip$/i', $this->original_filename) === 1) {
            return true;
        }
        return false;
    }

    public function getFormattedDateAttribute()
    {
        return $this->created_at->format('d/m/Y H:i');
    }

    public function getDiffForHumansAttribute()
    {
        return $this->created_at->diffForHumans();
    }

    public function isntChecklist()
    {
        return $this->subcategory != Paper::PAPER_CHECKLIST;
    }

    public function isChecklist()
    {
        return ! $this->isntChecklist();
    }

    public function getTeachingOfficeContact()
    {
        if ($this->course->isUestc()) {
            return option('teaching_office_contact_uestc');
        }
        return option('teaching_office_contact_glasgow');
    }

    public function getDisciplineContact()
    {
        return $this->course->discipline->contact;
    }
}
