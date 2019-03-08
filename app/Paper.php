<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Paper extends Model
{
    protected $guarded = [];

    protected $casts = [
        'approved_setter' => 'boolean',
    ];

    protected $appends = ['icon', 'formatted_date', 'diff_for_humans', 'formatted_size'];

    const VALID_CATEGORIES = ['main', 'resit'];

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

    public function scopeMain($query)
    {
        return $query->where('category', '=', 'main');
    }

    public function scopeResit($query)
    {
        return $query->where('category', '=', 'resit');
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
        return round($bytes, 1) . ' ' . $units[$pow];
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
        return !$this->subcategory == 'Paper Checklist';
    }
}
