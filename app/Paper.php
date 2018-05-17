<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Paper extends Model
{
    protected $guarded = [];

    const VALID_CATEGORIES = ['main', 'resit'];

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

    public function addComment($commentType, $comment)
    {
        $this->comments()->create([
            'user_id' => auth()->id(),
            'category' => $commentType,
            'comment' => $comment,
        ]);
    }
}
