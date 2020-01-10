<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PaperChecklist extends Model
{
    const CURRENT_VERSION = 1;

    protected $guarded = [];

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getPreviousChecklist()
    {
        return $this->where('id', '<', $this->id)->max('id');
    }

    public function getNextChecklist()
    {
        return $this->where('id', '>', $this->id)->min('id');
    }
}
