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
