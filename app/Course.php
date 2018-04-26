<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    protected $guarded = [];

    public function getFullNameAttribute()
    {
        return $this->code . ' ' . $this->title;
    }
}
